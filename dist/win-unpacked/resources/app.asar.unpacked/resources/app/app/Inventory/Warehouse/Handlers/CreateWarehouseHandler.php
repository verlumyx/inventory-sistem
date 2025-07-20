<?php

namespace App\Inventory\Warehouse\Handlers;

use App\Inventory\Warehouse\Contracts\WarehouseRepositoryInterface;
use App\Inventory\Warehouse\Exceptions\WarehouseOperationException;
use App\Inventory\Warehouse\Exceptions\WarehouseValidationException;
use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateWarehouseHandler
{
    /**
     * Create a new handler instance.
     */
    public function __construct(
        private WarehouseRepositoryInterface $warehouseRepository
    ) {}

    /**
     * Handle the creation of a new warehouse.
     *
     * @param array $data
     * @return Warehouse
     * @throws WarehouseValidationException
     * @throws WarehouseOperationException
     */
    public function handle(array $data): Warehouse
    {
        try {
            // Validar datos adicionales
            $this->validateBusinessRules($data);

            // Iniciar transacción
            return DB::transaction(function () use ($data) {
                // Crear el almacén
                $warehouse = $this->warehouseRepository->create($data);

                Log::info('Almacén creado exitosamente', [
                    'warehouse_id' => $warehouse->id,
                    'warehouse_code' => $warehouse->code,
                    'warehouse_name' => $warehouse->name,
                ]);

                return $warehouse;
            });

        } catch (WarehouseValidationException $e) {
            Log::warning('Error de validación al crear almacén', [
                'data' => $data,
                'errors' => $e->getErrors(),
            ]);
            throw $e;

        } catch (QueryException $e) {
            Log::error('Error de base de datos al crear almacén', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw WarehouseOperationException::databaseError('creación de almacén');

        } catch (\Exception $e) {
            Log::error('Error inesperado al crear almacén', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw WarehouseOperationException::creationFailed($e->getMessage());
        }
    }

    /**
     * Validate business rules for warehouse creation.
     *
     * @param array $data
     * @throws WarehouseValidationException
     */
    private function validateBusinessRules(array $data): void
    {
        // Validar que el código sea único si se proporciona
        if (isset($data['code']) && !$this->warehouseRepository->isCodeUnique($data['code'])) {
            throw WarehouseValidationException::duplicateCode($data['code']);
        }

        // Validar campos requeridos
        $requiredFields = ['name'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw WarehouseValidationException::requiredFields($missingFields);
        }

        // Validar estado si se proporciona
        if (isset($data['status']) && !is_bool($data['status'])) {
            throw WarehouseValidationException::invalidStatus();
        }
    }
}
