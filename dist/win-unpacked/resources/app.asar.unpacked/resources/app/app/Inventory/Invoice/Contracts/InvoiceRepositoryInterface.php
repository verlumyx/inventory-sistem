<?php

namespace App\Inventory\Invoice\Contracts;

use App\Inventory\Invoice\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface InvoiceRepositoryInterface
{
    /**
     * Get all invoices with optional filters.
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Get paginated invoices with optional filters.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find an invoice by ID.
     */
    public function findById(int $id): ?Invoice;

    /**
     * Find an invoice by code.
     */
    public function findByCode(string $code): ?Invoice;

    /**
     * Create a new invoice.
     */
    public function create(array $data): Invoice;

    /**
     * Update an existing invoice.
     */
    public function update(Invoice $invoice, array $data): Invoice;

    /**
     * Check if an invoice exists by ID.
     */
    public function exists(int $id): bool;

    /**
     * Check if a code is unique.
     */
    public function isCodeUnique(string $code, ?int $excludeId = null): bool;

    /**
     * Get invoices by warehouse.
     */
    public function getByWarehouse(int $warehouseId): Collection;

    /**
     * Count invoices with optional filters.
     */
    public function count(array $filters = []): int;

    /**
     * Search invoices by term.
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get filtered invoices with pagination.
     */
    public function getFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find invoice by ID or fail.
     */
    public function findOrFail(int $id): Invoice;

    /**
     * Delete an invoice.
     */
    public function delete(int $id): bool;

    /**
     * Get invoices for export.
     */
    public function getForExport(array $filters = []): Collection;
}
