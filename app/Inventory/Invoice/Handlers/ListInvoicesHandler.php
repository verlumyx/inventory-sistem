<?php

namespace App\Inventory\Invoice\Handlers;

use App\Inventory\Invoice\Contracts\InvoiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ListInvoicesHandler
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository
    ) {}

    /**
     * Handle the list all invoices request.
     */
    public function handleAll(array $filters = []): Collection
    {
        try {
            Log::info('Listando todas las facturas', [
                'filters' => $filters,
            ]);

            $invoices = $this->invoiceRepository->getAll($filters);

            Log::info('Facturas listadas exitosamente', [
                'count' => $invoices->count(),
                'filters' => $filters,
            ]);

            return $invoices;

        } catch (\Exception $e) {
            Log::error('Error al listar facturas', [
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle the list paginated invoices request.
     */
    public function handlePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            Log::info('Listando facturas paginadas', [
                'filters' => $filters,
                'per_page' => $perPage,
            ]);

            $invoices = $this->invoiceRepository->getPaginated($filters, $perPage);

            Log::info('Facturas paginadas listadas exitosamente', [
                'total' => $invoices->total(),
                'current_page' => $invoices->currentPage(),
                'per_page' => $perPage,
                'filters' => $filters,
            ]);

            return $invoices;

        } catch (\Exception $e) {
            Log::error('Error al listar facturas paginadas', [
                'filters' => $filters,
                'per_page' => $perPage,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle the search invoices request.
     */
    public function handleSearch(string $term, int $perPage = 15): LengthAwarePaginator
    {
        try {
            Log::info('Buscando facturas', [
                'term' => $term,
                'per_page' => $perPage,
            ]);

            $invoices = $this->invoiceRepository->search($term, $perPage);

            Log::info('Búsqueda de facturas completada', [
                'term' => $term,
                'results' => $invoices->total(),
                'per_page' => $perPage,
            ]);

            return $invoices;

        } catch (\Exception $e) {
            Log::error('Error al buscar facturas', [
                'term' => $term,
                'per_page' => $perPage,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle the count invoices request.
     */
    public function handleCount(array $filters = []): int
    {
        try {
            Log::info('Contando facturas', [
                'filters' => $filters,
            ]);

            $count = $this->invoiceRepository->count($filters);

            Log::info('Conteo de facturas completado', [
                'count' => $count,
                'filters' => $filters,
            ]);

            return $count;

        } catch (\Exception $e) {
            Log::error('Error al contar facturas', [
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
