<?php

namespace App\Inventory\Invoice\Handlers;

use App\Inventory\Invoice\Contracts\InvoiceRepositoryInterface;
use App\Inventory\Invoice\Models\Invoice;
use App\Inventory\Invoice\Exceptions\InvoiceNotFoundException;
use Illuminate\Support\Facades\Log;

class GetInvoiceHandler
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository
    ) {}

    /**
     * Handle the get invoice by ID request.
     */
    public function handleById(int $id): Invoice
    {
        try {
            Log::info('Obteniendo factura por ID', ['invoice_id' => $id]);

            $invoice = $this->invoiceRepository->findById($id);

            if (!$invoice) {
                throw InvoiceNotFoundException::byId($id);
            }

            Log::info('Factura obtenida exitosamente', [
                'invoice_id' => $invoice->id,
                'invoice_code' => $invoice->code,
            ]);

            return $invoice;

        } catch (InvoiceNotFoundException $e) {
            Log::warning('Factura no encontrada', [
                'invoice_id' => $id,
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error al obtener factura por ID', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle the get invoice by code request.
     */
    public function handleByCode(string $code): Invoice
    {
        try {
            Log::info('Obteniendo factura por código', ['invoice_code' => $code]);

            $invoice = $this->invoiceRepository->findByCode($code);

            if (!$invoice) {
                throw InvoiceNotFoundException::byCode($code);
            }

            Log::info('Factura obtenida exitosamente por código', [
                'invoice_id' => $invoice->id,
                'invoice_code' => $invoice->code,
            ]);

            return $invoice;

        } catch (InvoiceNotFoundException $e) {
            Log::warning('Factura no encontrada por código', [
                'invoice_code' => $code,
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error al obtener factura por código', [
                'invoice_code' => $code,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle the get invoice with items request.
     */
    public function handleWithItems(int $id): Invoice
    {
        try {
            Log::info('Obteniendo factura con items', ['invoice_id' => $id]);

            $invoice = $this->invoiceRepository->findById($id);

            if (!$invoice) {
                throw InvoiceNotFoundException::byId($id);
            }

            // Cargar items si no están cargados
            if (!$invoice->relationLoaded('invoiceItems')) {
                $invoice->load(['invoiceItems.item']);
            }

            Log::info('Factura con items obtenida exitosamente', [
                'invoice_id' => $invoice->id,
                'invoice_code' => $invoice->code,
                'items_count' => $invoice->invoiceItems->count(),
            ]);

            return $invoice;

        } catch (InvoiceNotFoundException $e) {
            Log::warning('Factura no encontrada para obtener con items', [
                'invoice_id' => $id,
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error al obtener factura con items', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle the check if invoice exists request.
     */
    public function handleExists(int $id): bool
    {
        try {
            Log::info('Verificando existencia de factura', ['invoice_id' => $id]);

            $exists = $this->invoiceRepository->exists($id);

            Log::info('Verificación de existencia completada', [
                'invoice_id' => $id,
                'exists' => $exists,
            ]);

            return $exists;

        } catch (\Exception $e) {
            Log::error('Error al verificar existencia de factura', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle the check if code is unique request.
     */
    public function handleIsCodeUnique(string $code, ?int $excludeId = null): bool
    {
        try {
            Log::info('Verificando unicidad de código de factura', [
                'code' => $code,
                'exclude_id' => $excludeId,
            ]);

            $isUnique = $this->invoiceRepository->isCodeUnique($code, $excludeId);

            Log::info('Verificación de unicidad completada', [
                'code' => $code,
                'exclude_id' => $excludeId,
                'is_unique' => $isUnique,
            ]);

            return $isUnique;

        } catch (\Exception $e) {
            Log::error('Error al verificar unicidad de código', [
                'code' => $code,
                'exclude_id' => $excludeId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
