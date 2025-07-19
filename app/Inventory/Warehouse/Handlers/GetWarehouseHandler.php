<?php

namespace App\Inventory\Warehouse\Handlers;

use App\Inventory\Warehouse\Contracts\WarehouseRepositoryInterface;
use App\Inventory\Warehouse\Exceptions\WarehouseNotFoundException;
use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Support\Facades\Log;

class GetWarehouseHandler
{
    /**
     * Create a new handler instance.
     */
    public function __construct(
        private WarehouseRepositoryInterface $warehouseRepository
    ) {}

    /**
     * Handle getting a warehouse by ID.
     *
     * @param int $id
     * @return Warehouse
     * @throws WarehouseNotFoundException
     */
    public function handleById(int $id): Warehouse
    {
        try {
            $warehouse = $this->warehouseRepository->findById($id);
            
            if (!$warehouse) {
                throw WarehouseNotFoundException::byId($id);
            }

            Log::info('Almacén obtenido exitosamente por ID', [
                'warehouse_id' => $warehouse->id,
                'warehouse_code' => $warehouse->code,
            ]);

            return $warehouse;

        } catch (WarehouseNotFoundException $e) {
            Log::warning('Almacén no encontrado por ID', [
                'warehouse_id' => $id,
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error inesperado al obtener almacén por ID', [
                'warehouse_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle getting a warehouse by code.
     *
     * @param string $code
     * @return Warehouse
     * @throws WarehouseNotFoundException
     */
    public function handleByCode(string $code): Warehouse
    {
        try {
            $warehouse = $this->warehouseRepository->findByCode($code);
            
            if (!$warehouse) {
                throw WarehouseNotFoundException::byCode($code);
            }

            Log::info('Almacén obtenido exitosamente por código', [
                'warehouse_id' => $warehouse->id,
                'warehouse_code' => $warehouse->code,
            ]);

            return $warehouse;

        } catch (WarehouseNotFoundException $e) {
            Log::warning('Almacén no encontrado por código', [
                'warehouse_code' => $code,
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error inesperado al obtener almacén por código', [
                'warehouse_code' => $code,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
