<?php

namespace App\Inventory\Entry\Contracts;

use App\Inventory\Entry\Models\Entry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface EntryRepositoryInterface
{
    /**
     * Get all entries.
     */
    public function all(): Collection;

    /**
     * Get entries with pagination.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get entries with filters and pagination.
     */
    public function getFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find an entry by ID.
     */
    public function find(int $id): ?Entry;

    /**
     * Find an entry by ID or fail.
     */
    public function findOrFail(int $id): Entry;

    /**
     * Find an entry by code.
     */
    public function findByCode(string $code): ?Entry;

    /**
     * Create a new entry.
     */
    public function create(array $data): Entry;

    /**
     * Update an existing entry.
     */
    public function update(int $id, array $data): Entry;

    /**
     * Get active entries.
     */
    public function getActive(): Collection;

    /**
     * Get inactive entries.
     */
    public function getInactive(): Collection;

    /**
     * Search entries by term.
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if a code is unique.
     */
    public function isCodeUnique(string $code, ?int $excludeId = null): bool;

    /**
     * Activate an entry.
     */
    public function activate(int $id): bool;

    /**
     * Deactivate an entry.
     */
    public function deactivate(int $id): bool;

    /**
     * Toggle entry status.
     */
    public function toggleStatus(int $id): bool;

    /**
     * Get entries count.
     */
    public function count(): int;

    /**
     * Get active entries count.
     */
    public function countActive(): int;

    /**
     * Get inactive entries count.
     */
    public function countInactive(): int;

    /**
     * Get entries created today.
     */
    public function getCreatedToday(): Collection;

    /**
     * Get entries created this week.
     */
    public function getCreatedThisWeek(): Collection;

    /**
     * Get entries created this month.
     */
    public function getCreatedThisMonth(): Collection;

    /**
     * Get the latest entries.
     */
    public function getLatest(int $limit = 10): Collection;

    /**
     * Get entries by status with pagination.
     */
    public function getByStatus(bool $status, int $perPage = 15): LengthAwarePaginator;

    /**
     * Bulk update entries status.
     */
    public function bulkUpdateStatus(array $ids, bool $status): int;

    /**
     * Get entries for export.
     */
    public function getForExport(array $filters = []): Collection;

    /**
     * Get entries statistics.
     */
    public function getStatistics(): array;

    /**
     * Create entry with items.
     */
    public function createWithItems(array $entryData, array $items): Entry;

    /**
     * Update entry with items.
     */
    public function updateWithItems(int $id, array $entryData, array $items): Entry;

    /**
     * Get entry with items.
     */
    public function getWithItems(int $id): Entry;
}
