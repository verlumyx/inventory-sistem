<?php

namespace App\Inventory\Adjustments\Handlers;

use App\Inventory\Adjustments\Contracts\AdjustmentRepositoryInterface;
use App\Inventory\Adjustments\Models\Adjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateAdjustmentHandler
{
    public function __construct(
        private AdjustmentRepositoryInterface $repository
    ) {}

    public function handle(int $id, array $data): Adjustment
    {
        try {
            $adjustment = $this->repository->findById($id);
            if (!$adjustment) {
                throw new \RuntimeException("Ajuste con ID {$id} no encontrado");
            }

            return DB::transaction(function () use ($adjustment, $data) {
                $adjustment = $this->repository->update($adjustment, [
                    'description' => $data['description'] ?? $adjustment->description,
                    'warehouse_id' => $data['warehouse_id'] ?? $adjustment->warehouse_id,
                    'type' => $data['type'] ?? $adjustment->type,
                    // El status no se actualiza aquí, solo con los métodos específicos
                ]);

                Log::info('Ajuste actualizado', [
                    'adjustment_id' => $adjustment->id,
                    'code' => $adjustment->code,
                ]);

                return $adjustment->load(['warehouse', 'items.item']);
            });
        } catch (\Exception $e) {
            Log::error('Error al actualizar ajuste', ['id' => $id, 'data' => $data, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function handleWithItems(int $id, array $data, array $items): Adjustment
    {
        try {
            $adjustment = $this->repository->findById($id);
            if (!$adjustment) {
                throw new \RuntimeException("Ajuste con ID {$id} no encontrado");
            }

            return DB::transaction(function () use ($adjustment, $data, $items) {
                // Actualizar datos del ajuste
                $adjustment = $this->repository->update($adjustment, [
                    'description' => $data['description'] ?? $adjustment->description,
                    'warehouse_id' => $data['warehouse_id'] ?? $adjustment->warehouse_id,
                    'type' => $data['type'] ?? $adjustment->type,
                    // El status no se actualiza aquí, solo con los métodos específicos
                ]);

                // Eliminar items existentes
                $adjustment->items()->delete();

                // Crear nuevos items
                foreach ($items as $row) {
                    $adjustment->items()->create([
                        'item_id' => (int) $row['item_id'],
                        'amount' => (float) $row['amount'],
                    ]);
                }

                Log::info('Ajuste actualizado con items', [
                    'adjustment_id' => $adjustment->id,
                    'code' => $adjustment->code,
                    'items_count' => count($items),
                ]);

                return $adjustment->load(['warehouse', 'items.item']);
            });
        } catch (\Exception $e) {
            Log::error('Error al actualizar ajuste con items', ['id' => $id, 'data' => $data, 'items' => $items, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}

