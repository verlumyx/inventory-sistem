<?php

namespace App\Inventory\Invoice\Handlers;

use App\Inventory\Invoice\Contracts\InvoiceRepositoryInterface;
use App\Inventory\Invoice\Models\Invoice;
use App\Inventory\Invoice\Models\InvoiceItem;
use App\Inventory\Invoice\Exceptions\InvoiceValidationException;
use App\Inventory\Invoice\Exceptions\InvoiceOperationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CreateInvoiceHandler
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository
    ) {}

    /**
     * Handle the create invoice request.
     */
    public function handle(array $data): Invoice
    {
        try {
            Log::info('Creando factura', [
                'data' => $data,
            ]);

            // Validar datos adicionales
            $this->validateBusinessRules($data);

            // Iniciar transacción
            return DB::transaction(function () use ($data) {
                // Crear la factura
                $invoice = $this->invoiceRepository->create([
                    'warehouse_id' => $data['warehouse_id'],
                ]);

                Log::info('Factura creada exitosamente', [
                    'invoice_id' => $invoice->id,
                    'invoice_code' => $invoice->code,
                    'warehouse_id' => $invoice->warehouse_id,
                ]);

                return $invoice->load(['warehouse', 'invoiceItems.item']);
            });

        } catch (InvoiceValidationException $e) {
            Log::warning('Error de validación al crear factura', [
                'data' => $data,
                'errors' => $e->getErrors(),
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error al crear factura', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw new InvoiceOperationException(
                'Error interno al crear la factura. Por favor, inténtalo de nuevo.',
                0,
                $e
            );
        }
    }

    /**
     * Handle the create invoice with items request.
     */
    public function handleWithItems(array $invoiceData, array $items): Invoice
    {
        try {
            Log::info('Creando factura con items', [
                'invoice_data' => $invoiceData,
                'items_count' => count($items),
            ]);

            // Validar items
            $this->validateItems($items);

            // Iniciar transacción
            return DB::transaction(function () use ($invoiceData, $items) {
                // Crear la factura
                $invoice = $this->invoiceRepository->create([
                    'warehouse_id' => $invoiceData['warehouse_id'],
                ]);

                // Crear los items de la factura
                foreach ($items as $itemData) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_id' => $itemData['item_id'],
                        'amount' => $itemData['amount'],
                        'price' => $itemData['price'],
                    ]);
                }

                Log::info('Factura con items creada exitosamente', [
                    'invoice_id' => $invoice->id,
                    'invoice_code' => $invoice->code,
                    'items_count' => count($items),
                ]);

                return $invoice->load(['warehouse', 'invoiceItems.item']);
            });

        } catch (InvoiceValidationException $e) {
            Log::warning('Error de validación al crear factura con items', [
                'invoice_data' => $invoiceData,
                'items' => $items,
                'errors' => $e->getErrors(),
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error al crear factura con items', [
                'invoice_data' => $invoiceData,
                'items' => $items,
                'error' => $e->getMessage(),
            ]);
            throw new InvoiceOperationException(
                'Error interno al crear la factura con items. Por favor, inténtalo de nuevo.',
                0,
                $e
            );
        }
    }

    /**
     * Validate business rules for invoice creation.
     */
    private function validateBusinessRules(array $data): void
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
     * Validate items for invoice creation.
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
