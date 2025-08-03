<?php

namespace App\Inventory\Warehouse\Handlers;

use App\Inventory\Warehouse\Contracts\WarehouseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ListWarehousesHandler
{
    /**
     * Create a new handler instance.
     */
    public function __construct(
        private WarehouseRepositoryInterface $warehouseRepository
    ) {}

    /**
     * Handle getting all warehouses with filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function handleAll(array $filters = []): Collection
    {
        try {
            $warehouses = $this->warehouseRepository->getAll($filters);

            Log::info('Lista de almacenes obtenida exitosamente', [
                'total_warehouses' => $warehouses->count(),
                'filters' => $filters,
            ]);

            return $warehouses;

        } catch (\Exception $e) {
            Log::error('Error al obtener lista de almacenes', [
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle getting paginated warehouses with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function handlePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            $warehouses = $this->warehouseRepository->getPaginated($filters, $perPage);

            Log::info('Lista paginada de almacenes obtenida exitosamente', [
                'total_warehouses' => $warehouses->total(),
                'current_page' => $warehouses->currentPage(),
                'per_page' => $warehouses->perPage(),
                'filters' => $filters,
            ]);

            return $warehouses;

        } catch (\Exception $e) {
            Log::error('Error al obtener lista paginada de almacenes', [
                'filters' => $filters,
                'per_page' => $perPage,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle getting active warehouses only.
     *
     * @return Collection
     */
    public function handleActive(): Collection
    {
        try {
            $warehouses = $this->warehouseRepository->getActive();

            Log::info('Lista de almacenes activos obtenida exitosamente', [
                'total_active_warehouses' => $warehouses->count(),
            ]);

            return $warehouses;

        } catch (\Exception $e) {
            Log::error('Error al obtener almacenes activos', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle getting inactive warehouses only.
     *
     * @return Collection
     */
    public function handleInactive(): Collection
    {
        try {
            $warehouses = $this->warehouseRepository->getInactive();

            Log::info('Lista de almacenes inactivos obtenida exitosamente', [
                'total_inactive_warehouses' => $warehouses->count(),
            ]);

            return $warehouses;

        } catch (\Exception $e) {
            Log::error('Error al obtener almacenes inactivos', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle getting warehouse count with filters.
     *
     * @param array $filters
     * @return int
     */
    public function handleCount(array $filters = []): int
    {
        try {
            $count = $this->warehouseRepository->count($filters);

            Log::info('Conteo de almacenes obtenido exitosamente', [
                'total_count' => $count,
                'filters' => $filters,
            ]);

            return $count;

        } catch (\Exception $e) {
            Log::error('Error al obtener conteo de almacenes', [
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
