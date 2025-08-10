<?php

namespace App\Inventory\Adjustments\Handlers;

use App\Inventory\Adjustments\Contracts\AdjustmentRepositoryInterface;
use App\Inventory\Adjustments\Models\Adjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateAdjustmentHandler
{
    public function __construct(
        private AdjustmentRepositoryInterface $repository
    ) {}

    public function handle(array $data): Adjustment
    {
        try {
            return DB::transaction(function () use ($data) {
                $adjustment = $this->repository->create([
                    'description' => $data['description'] ?? null,
                    'warehouse_id' => $data['warehouse_id'],
                    'type' => $data['type'] ?? 'positive',
                    'status' => 0, // Siempre se crea como pendiente
                ]);

                Log::info('Ajuste creado', [
                    'adjustment_id' => $adjustment->id,
                    'code' => $adjustment->code,
                ]);

                return $adjustment->load(['warehouse', 'items.item']);
            });
        } catch (\Exception $e) {
            Log::error('Error al crear ajuste', ['data' => $data, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Crear ajuste con items.
     */
    public function handleWithItems(array $data, array $items): Adjustment
    {
        try {
            return DB::transaction(function () use ($data, $items) {
                // Crear el ajuste con items usando el repositorio
                $adjustment = $this->repository->createWithItems([
                    'description' => $data['description'] ?? null,
                    'warehouse_id' => $data['warehouse_id'],
                    'type' => $data['type'] ?? 'positive',
                    'status' => 0, // Siempre se crea como pendiente
                ], $items);

                // Ya regresa cargado con relaciones

                Log::info('Ajuste con items creado', [
                    'adjustment_id' => $adjustment->id,
                    'code' => $adjustment->code,
                    'items_count' => count($items),
                ]);

                return $adjustment->load(['warehouse', 'items.item']);
            });
        } catch (\Exception $e) {
            Log::error('Error al crear ajuste con items', ['data' => $data, 'items' => $items, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

}

