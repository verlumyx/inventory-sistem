<?php

namespace App\Inventory\Warehouse\Repositories;

use App\Inventory\Warehouse\Contracts\WarehouseRepositoryInterface;
use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class WarehouseRepository implements WarehouseRepositoryInterface
{
    /**
     * Get all warehouses with optional filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Warehouse::getFiltered($filters);

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        return $query->orderBy($sortBy, $sortDirection)->get();
    }

    /**
     * Get paginated warehouses with optional filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Warehouse::getFiltered($filters);

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'code';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        return $query->orderBy($sortBy, $sortDirection)->paginate($perPage);
    }

    /**
     * Find a warehouse by ID.
     *
     * @param int $id
     * @return Warehouse|null
     */
    public function findById(int $id): ?Warehouse
    {
        return Warehouse::find($id);
    }

    /**
     * Find a warehouse by code.
     *
     * @param string $code
     * @return Warehouse|null
     */
    public function findByCode(string $code): ?Warehouse
    {
        return Warehouse::findByCode($code);
    }

    /**
     * Create a new warehouse.
     *
     * @param array $data
     * @return Warehouse
     */
    public function create(array $data): Warehouse
    {
        return Warehouse::create($data);
    }

    /**
     * Update a warehouse.
     *
     * @param Warehouse $warehouse
     * @param array $data
     * @return Warehouse
     */
    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update($data);
        return $warehouse->fresh();
    }

    /**
     * Check if a warehouse exists by ID.
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        return Warehouse::where('id', $id)->exists();
    }

    /**
     * Check if a warehouse code is unique.
     *
     * @param string $code
     * @param int|null $excludeId
     * @return bool
     */
    public function isCodeUnique(string $code, ?int $excludeId = null): bool
    {
        return Warehouse::isCodeUnique($code, $excludeId);
    }

    /**
     * Get active warehouses only.
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return Warehouse::active()->orderBy('name')->get();
    }

    /**
     * Get inactive warehouses only.
     *
     * @return Collection
     */
    public function getInactive(): Collection
    {
        return Warehouse::inactive()->orderBy('name')->get();
    }

    /**
     * Count total warehouses.
     *
     * @param array $filters
     * @return int
     */
    public function count(array $filters = []): int
    {
        return Warehouse::getFiltered($filters)->count();
    }


}
