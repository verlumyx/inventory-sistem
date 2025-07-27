<?php

namespace App\Inventory\Invoice\Handlers;

use App\Inventory\Invoice\Contracts\InvoiceRepositoryInterface;
use App\Inventory\Invoice\Models\Invoice;
use App\Inventory\Invoice\Models\InvoiceItem;
use App\Inventory\Invoice\Exceptions\InvoiceNotFoundException;
use App\Inventory\Invoice\Exceptions\InvoiceValidationException;
use App\Inventory\Invoice\Exceptions\InvoiceOperationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UpdateInvoiceHandler
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository
    ) {}

    /**
     * Handle the update invoice request.
     */
    public function handle(int $id, array $data): Invoice
    {
        try {
            Log::info('Actualizando factura', [
                'invoice_id' => $id,
                'data' => $data,
            ]);

            // Verificar que la factura existe
            $invoice = $this->invoiceRepository->findById($id);
            if (!$invoice) {
                throw new InvoiceNotFoundException("Factura con ID {$id} no encontrada.");
            }

            // Validar datos adicionales
            $this->validateBusinessRules($data, $id);

            // Actualizar la factura
            $updatedInvoice = $this->invoiceRepository->update($invoice, $data);

            Log::info('Factura actualizada exitosamente', [
                'invoice_id' => $updatedInvoice->id,
                'invoice_code' => $updatedInvoice->code,
            ]);

            return $updatedInvoice;

        } catch (InvoiceNotFoundException $e) {
            Log::warning('Factura no encontrada para actualizar', [
                'invoice_id' => $id,
            ]);
            throw $e;

        } catch (InvoiceValidationException $e) {
            Log::warning('Error de validación al actualizar factura', [
                'invoice_id' => $id,
                'data' => $data,
                'errors' => $e->getErrors(),
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error al actualizar factura', [
                'invoice_id' => $id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw new InvoiceOperationException(
                'Error interno al actualizar la factura. Por favor, inténtalo de nuevo.',
                0,
                $e
            );
        }
    }

    /**
     * Handle the update invoice with items request.
     */
    public function handleWithItems(int $id, array $invoiceData, array $items): Invoice
    {
        try {
            Log::info('Actualizando factura con items', [
                'invoice_id' => $id,
                'invoice_data' => $invoiceData,
                'items_count' => count($items),
            ]);

            // Verificar que la factura existe
            $invoice = $this->invoiceRepository->findById($id);
            if (!$invoice) {
                throw new InvoiceNotFoundException("Factura con ID {$id} no encontrada.");
            }

            // Validar items
            $this->validateItems($items);

            // Iniciar transacción
            return DB::transaction(function () use ($invoice, $invoiceData, $items) {
                // Actualizar la factura
                $updatedInvoice = $this->invoiceRepository->update($invoice, $invoiceData);

                // Eliminar items existentes
                $invoice->invoiceItems()->delete();

                // Crear los nuevos items
                foreach ($items as $itemData) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_id' => $itemData['item_id'],
                        'amount' => $itemData['amount'],
                        'price' => $itemData['price'],
                    ]);
                }

                Log::info('Factura con items actualizada exitosamente', [
                    'invoice_id' => $updatedInvoice->id,
                    'invoice_code' => $updatedInvoice->code,
                    'items_count' => count($items),
                ]);

                return $updatedInvoice->load(['warehouse', 'invoiceItems.item']);
            });

        } catch (InvoiceNotFoundException $e) {
            Log::warning('Factura no encontrada para actualizar con items', [
                'invoice_id' => $id,
            ]);
            throw $e;

        } catch (InvoiceValidationException $e) {
            Log::warning('Error de validación al actualizar factura con items', [
                'invoice_id' => $id,
                'invoice_data' => $invoiceData,
                'items' => $items,
                'errors' => $e->getErrors(),
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error al actualizar factura con items', [
                'invoice_id' => $id,
                'invoice_data' => $invoiceData,
                'items' => $items,
                'error' => $e->getMessage(),
            ]);
            throw new InvoiceOperationException(
                'Error interno al actualizar la factura con items. Por favor, inténtalo de nuevo.',
                0,
                $e
            );
        }
    }

    /**
     * Validate business rules for invoice update.
     */
    private function validateBusinessRules(array $data, int $excludeId): void
    {
        $errors = [];

        // Validar que el warehouse existe
        if (!empty($data['warehouse_id'])) {
            $warehouseExists = \App\Inventory\Warehouse\Models\Warehouse::where('id', $data['warehouse_id'])->exists();
            if (!$warehouseExists) {
                $errors['warehouse_id'] = 'El almacén seleccionado no existe.';
            }
        }

        if (!empty($errors)) {
            throw new InvoiceValidationException('Errores de validación en la factura', $errors);
        }
    }

    /**
     * Validate items for invoice update.
     */
    private function validateItems(array $items): void
    {
        $errors = [];

        if (empty($items)) {
            $errors['items'] = 'La factura debe tener al menos un item.';
        }

        foreach ($items as $index => $item) {
            $itemErrors = [];

            if (empty($item['item_id'])) {
                $itemErrors['item_id'] = 'El item es requerido.';
            } else {
                $itemExists = \App\Inventory\Item\Models\Item::where('id', $item['item_id'])->exists();
                if (!$itemExists) {
                    $itemErrors['item_id'] = 'El item seleccionado no existe.';
                }
            }

            if (empty($item['amount']) || $item['amount'] <= 0) {
                $itemErrors['amount'] = 'La cantidad debe ser mayor a 0.';
            }

            if (empty($item['price']) || $item['price'] <= 0) {
                $itemErrors['price'] = 'El precio debe ser mayor a 0.';
            }

            if (!empty($itemErrors)) {
                $errors["items.{$index}"] = $itemErrors;
            }
        }

        // Validar items duplicados
        $itemIds = array_column($items, 'item_id');
        $duplicates = array_diff_assoc($itemIds, array_unique($itemIds));
        
        if (!empty($duplicates)) {
            $errors['items'] = 'No se pueden agregar items duplicados en la misma factura.';
        }

        if (!empty($errors)) {
            throw new InvoiceValidationException('Errores de validación en los items', $errors);
        }
    }
}
