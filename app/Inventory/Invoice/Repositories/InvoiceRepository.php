<?php

namespace App\Inventory\Invoice\Repositories;

use App\Inventory\Invoice\Contracts\InvoiceRepositoryInterface;
use App\Inventory\Invoice\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    /**
     * Get all invoices with optional filters.
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Invoice::query()->with(['warehouse', 'invoiceItems.item']);

        // Filtro por status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filtro por rango de fechas
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Filtro por mes y año específico
        if (isset($filters['month']) && isset($filters['year'])) {
            $query->whereMonth('created_at', $filters['month'])
                  ->whereYear('created_at', $filters['year']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get paginated invoices with optional filters.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Invoice::getFiltered($filters)
            ->with(['warehouse', 'invoiceItems.item'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find an invoice by ID.
     */
    public function findById(int $id): ?Invoice
    {
        return Invoice::with(['warehouse', 'invoiceItems.item'])->find($id);
    }

    /**
     * Find an invoice by code.
     */
    public function findByCode(string $code): ?Invoice
    {
        return Invoice::with(['warehouse', 'invoiceItems.item'])
            ->where('code', $code)
            ->first();
    }

    /**
     * Create a new invoice.
     */
    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    /**
     * Update an existing invoice.
     */
    public function update(Invoice $invoice, array $data): Invoice
    {
        $invoice->update($data);
        return $invoice->fresh(['warehouse', 'invoiceItems.item']);
    }

    /**
     * Check if an invoice exists by ID.
     */
    public function exists(int $id): bool
    {
        return Invoice::where('id', $id)->exists();
    }

    /**
     * Check if a code is unique.
     */
    public function isCodeUnique(string $code, ?int $excludeId = null): bool
    {
        return Invoice::isCodeUnique($code, $excludeId);
    }

    /**
     * Get invoices by warehouse.
     */
    public function getByWarehouse(int $warehouseId): Collection
    {
        return Invoice::with(['warehouse', 'invoiceItems.item'])
            ->where('warehouse_id', $warehouseId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Count invoices with optional filters.
     */
    public function count(array $filters = []): int
    {
        return Invoice::getFiltered($filters)->count();
    }

    /**
     * Search invoices by term.
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return Invoice::search($term)
            ->with(['warehouse', 'invoiceItems.item'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get filtered invoices with pagination.
     */
    public function getFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->getPaginated($filters, $perPage);
    }

    /**
     * Find invoice by ID or fail.
     */
    public function findOrFail(int $id): Invoice
    {
        return Invoice::with(['warehouse', 'invoiceItems.item'])->findOrFail($id);
    }

    /**
     * Delete an invoice.
     */
    public function delete(int $id): bool
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return false;
        }

        return $invoice->delete();
    }

    /**
     * Get invoices for export.
     */
    public function getForExport(array $filters = []): Collection
    {
        return Invoice::getFiltered($filters)
            ->with(['warehouse', 'invoiceItems.item'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
