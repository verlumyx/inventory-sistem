<?php

namespace App\Inventory\Warehouse\Handlers;

use App\Inventory\Warehouse\Contracts\WarehouseRepositoryInterface;
use App\Inventory\Warehouse\Exceptions\WarehouseNotFoundException;
use App\Inventory\Warehouse\Exceptions\WarehouseOperationException;
use App\Inventory\Warehouse\Exceptions\WarehouseValidationException;
use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateWarehouseHandler
{
    /**
     * Create a new handler instance.
     */
    public function __construct(
        private WarehouseRepositoryInterface $warehouseRepository
    ) {}

    /**
     * Handle the update of a warehouse.
     *
     * @param int $id
     * @param array $data
     * @return Warehouse
     * @throws WarehouseNotFoundException
     * @throws WarehouseValidationException
     * @throws WarehouseOperationException
     */
    public function handle(int $id, array $data): Warehouse
    {
        try {
            // Buscar el almacén
            $warehouse = $this->warehouseRepository->findById($id);
            
            if (!$warehouse) {
                throw WarehouseNotFoundException::byId($id);
            }

            // Validar datos adicionales
            $this->validateBusinessRules($data, $warehouse);

            // Iniciar transacción
            return DB::transaction(function () use ($warehouse, $data) {
                // Actualizar el almacén
                $updatedWarehouse = $this->warehouseRepository->update($warehouse, $data);

                Log::info('Almacén actualizado exitosamente', [
                    'warehouse_id' => $updatedWarehouse->id,
                    'warehouse_code' => $updatedWarehouse->code,
                    'warehouse_name' => $updatedWarehouse->name,
                    'updated_data' => $data,
                ]);

                return $updatedWarehouse;
            });

        } catch (WarehouseNotFoundException | WarehouseValidationException $e) {
            Log::warning('Error al actualizar almacén', [
                'warehouse_id' => $id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;

        } catch (QueryException $e) {
            Log::error('Error de base de datos al actualizar almacén', [
                'warehouse_id' => $id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw WarehouseOperationException::databaseError('actualización de almacén');

        } catch (\Exception $e) {
            Log::error('Error inesperado al actualizar almacén', [
                'warehouse_id' => $id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw WarehouseOperationException::updateFailed($id, $e->getMessage());
        }
    }

    /**
     * Validate business rules for warehouse update.
     *
     * @param array $data
     * @param Warehouse $warehouse
     * @throws WarehouseValidationException
     */
    private function validateBusinessRules(array $data, Warehouse $warehouse): void
    {
        // Validar que el código sea único si se proporciona y es diferente al actual
        if (isset($data['code']) && $data['code'] !== $warehouse->code) {
            if (!$this->warehouseRepository->isCodeUnique($data['code'], $warehouse->id)) {
                throw WarehouseValidationException::duplicateCode($data['code']);
            }
        }

        // Validar estado si se proporciona
        if (isset($data['status']) && !is_bool($data['status'])) {
            throw WarehouseValidationException::invalidStatus();
        }
    }
}
