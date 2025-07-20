<?php

namespace App\Inventory\Warehouse\Contracts;

use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface WarehouseRepositoryInterface
{
    /**
     * Get all warehouses with optional filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Get paginated warehouses with optional filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a warehouse by ID.
     *
     * @param int $id
     * @return Warehouse|null
     */
    public function findById(int $id): ?Warehouse;

    /**
     * Find a warehouse by code.
     *
     * @param string $code
     * @return Warehouse|null
     */
    public function findByCode(string $code): ?Warehouse;

    /**
     * Create a new warehouse.
     *
     * @param array $data
     * @return Warehouse
     */
    public function create(array $data): Warehouse;

    /**
     * Update a warehouse.
     *
     * @param Warehouse $warehouse
     * @param array $data
     * @return Warehouse
     */
    public function update(Warehouse $warehouse, array $data): Warehouse;

    /**
     * Check if a warehouse exists by ID.
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool;

    /**
     * Check if a warehouse code is unique.
     *
     * @param string $code
     * @param int|null $excludeId
     * @return bool
     */
    public function isCodeUnique(string $code, ?int $excludeId = null): bool;

    /**
     * Get active warehouses only.
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Get inactive warehouses only.
     *
     * @return Collection
     */
    public function getInactive(): Collection;

    /**
     * Count total warehouses.
     *
     * @param array $filters
     * @return int
     */
    public function count(array $filters = []): int;
}
