<?php

namespace App\Inventory\Adjustments\Services;

use App\Inventory\Adjustments\Models\Adjustment;
use App\Inventory\WarehouseItem\Models\WarehouseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdjustmentInventoryService
{
    /**
     * Aplicar un ajuste al inventario
     */
    public function applyAdjustment(Adjustment $adjustment): array
    {
        $results = [];
        $errors = [];

        // Validar que el ajuste esté pendiente
        if ($adjustment->status !== 0) {
            throw new \RuntimeException('Solo se pueden aplicar ajustes pendientes');
        }

        // Validar que tenga items
        if ($adjustment->items->isEmpty()) {
            throw new \RuntimeException('El ajuste no tiene items para aplicar');
        }

        // Para ajustes negativos, validar que hay suficiente stock antes de aplicar
        if ($adjustment->type === 'negative') {
            $stockValidation = $this->validateNegativeAdjustment($adjustment);
            if (!$stockValidation['valid']) {
                return [
                    'success' => false,
                    'errors' => $stockValidation['errors'],
                    'results' => []
                ];
            }
        }

        // Aplicar cada item del ajuste
        foreach ($adjustment->items as $adjustmentItem) {
            try {
                $result = $this->applyAdjustmentItem(
                    $adjustment->warehouse_id,
                    $adjustmentItem->item_id,
                    $adjustmentItem->amount,
                    $adjustment->type
                );
                
                $results[] = $result;
                
                Log::info('Item de ajuste aplicado', [
                    'adjustment_id' => $adjustment->id,
                    'item_id' => $adjustmentItem->item_id,
                    'amount' => $adjustmentItem->amount,
                    'type' => $adjustment->type,
                    'result' => $result
                ]);
                
            } catch (\Exception $e) {
                $itemName = $adjustmentItem->item->display_name ?? "Item ID {$adjustmentItem->item_id}";
                $error = "Error aplicando item {$itemName}: {$e->getMessage()}";
                $errors[] = $error;

                Log::error('Error aplicando item de ajuste', [
                    'adjustment_id' => $adjustment->id,
                    'item_id' => $adjustmentItem->item_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'success' => empty($errors),
            'errors' => $errors,
            'results' => $results
        ];
    }

    /**
     * Validar que un ajuste negativo no cause stock negativo
     */
    private function validateNegativeAdjustment(Adjustment $adjustment): array
    {
        $errors = [];

        foreach ($adjustment->items as $adjustmentItem) {
            $warehouseItem = WarehouseItem::where('warehouse_id', $adjustment->warehouse_id)
                ->where('item_id', $adjustmentItem->item_id)
                ->first();

            $currentStock = $warehouseItem ? $warehouseItem->quantity_available : 0;

            if ($currentStock < $adjustmentItem->amount) {
                $itemName = $adjustmentItem->item->display_name ?? "Item ID {$adjustmentItem->item_id}";
                $errors[] = "Item {$itemName}: Stock actual ({$currentStock}) es menor que la cantidad a restar ({$adjustmentItem->amount})";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Aplicar un item individual del ajuste
     */
    private function applyAdjustmentItem(int $warehouseId, int $itemId, float $amount, string $type): array
    {
        // Buscar o crear el registro de warehouse_item
        $warehouseItem = WarehouseItem::findOrCreate($warehouseId, $itemId, 0);
        
        $previousStock = $warehouseItem->quantity_available;
        
        if ($type === 'positive') {
            // Ajuste positivo: aumentar stock
            $success = $warehouseItem->addStock($amount);
            $operation = 'add';
        } else {
            // Ajuste negativo: disminuir stock
            $success = $warehouseItem->removeStock($amount);
            $operation = 'remove';
        }

        if (!$success) {
            throw new \RuntimeException("No se pudo aplicar el ajuste al item");
        }

        // Recargar para obtener el stock actualizado
        $warehouseItem->refresh();
        $newStock = $warehouseItem->quantity_available;

        return [
            'warehouse_item_id' => $warehouseItem->id,
            'item_id' => $itemId,
            'warehouse_id' => $warehouseId,
            'operation' => $operation,
            'amount' => $amount,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'difference' => $newStock - $previousStock
        ];
    }
}
