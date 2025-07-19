<?php

namespace App\Inventory\Item\Contracts;

use App\Inventory\Item\Models\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ItemRepositoryInterface
{
    /**
     * Get all items.
     */
    public function all(): Collection;

    /**
     * Get items with pagination.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get items with filters and pagination.
     */
    public function getFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find an item by ID.
     */
    public function find(int $id): ?Item;

    /**
     * Find an item by ID or fail.
     */
    public function findOrFail(int $id): Item;

    /**
     * Find an item by code.
     */
    public function findByCode(string $code): ?Item;

    /**
     * Find an item by QR code.
     */
    public function findByQrCode(string $qrCode): ?Item;

    /**
     * Create a new item.
     */
    public function create(array $data): Item;

    /**
     * Update an existing item.
     */
    public function update(int $id, array $data): Item;



    /**
     * Get active items.
     */
    public function getActive(): Collection;

    /**
     * Get inactive items.
     */
    public function getInactive(): Collection;

    /**
     * Search items by term.
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if a code is unique.
     */
    public function isCodeUnique(string $code, ?int $excludeId = null): bool;

    /**
     * Check if a QR code is unique.
     */
    public function isQrCodeUnique(string $qrCode, ?int $excludeId = null): bool;

    /**
     * Activate an item.
     */
    public function activate(int $id): bool;

    /**
     * Deactivate an item.
     */
    public function deactivate(int $id): bool;

    /**
     * Toggle item status.
     */
    public function toggleStatus(int $id): bool;

    /**
     * Get items count.
     */
    public function count(): int;

    /**
     * Get active items count.
     */
    public function countActive(): int;

    /**
     * Get inactive items count.
     */
    public function countInactive(): int;

    /**
     * Get items created today.
     */
    public function getCreatedToday(): Collection;

    /**
     * Get items created this week.
     */
    public function getCreatedThisWeek(): Collection;

    /**
     * Get items created this month.
     */
    public function getCreatedThisMonth(): Collection;

    /**
     * Get the latest items.
     */
    public function getLatest(int $limit = 10): Collection;

    /**
     * Get items by status with pagination.
     */
    public function getByStatus(bool $status, int $perPage = 15): LengthAwarePaginator;

    /**
     * Bulk update items status.
     */
    public function bulkUpdateStatus(array $ids, bool $status): int;



    /**
     * Get items for export.
     */
    public function getForExport(array $filters = []): Collection;

    /**
     * Get items statistics.
     */
    public function getStatistics(): array;
}
