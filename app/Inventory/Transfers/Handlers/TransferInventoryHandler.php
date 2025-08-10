<?php

namespace App\Inventory\Transfers\Handlers;

use App\Inventory\Transfers\Models\Transfer;
use App\Inventory\WarehouseItem\Models\WarehouseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferInventoryHandler
{
    /**
     * When transfer is completed (status=1), move stock from source to destination.
     */
    public function handleCompletion(Transfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            Log::info('Moviendo inventario por traslado completado', [
                'transfer_id' => $transfer->id,
                'source' => $transfer->warehouse_source_id,
                'destination' => $transfer->warehouse_destination_id,
            ]);

            foreach ($transfer->items as $tItem) {
                $this->moveStock(
                    $transfer->warehouse_source_id,
                    $transfer->warehouse_destination_id,
                    $tItem->item_id,
                    (float)$tItem->amount
                );
            }
        });
    }

    /**
     * If transfer is reverted to pending (status=0), undo the move: return stock from destination to source.
     */
    public function handleRevert(Transfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            Log::info('Revirtiendo inventario por traslado', [
                'transfer_id' => $transfer->id,
                'source' => $transfer->warehouse_source_id,
                'destination' => $transfer->warehouse_destination_id,
            ]);

            foreach ($transfer->items as $tItem) {
                $this->moveStock(
                    $transfer->warehouse_destination_id,
                    $transfer->warehouse_source_id,
                    $tItem->item_id,
                    (float)$tItem->amount
                );
            }
        });
    }

    private function moveStock(int $fromWarehouseId, int $toWarehouseId, int $itemId, float $quantity): void
    {
        // Ensure records exist
        $from = WarehouseItem::firstOrCreate([
            'warehouse_id' => $fromWarehouseId,
            'item_id' => $itemId,
        ], ['quantity_available' => 0]);

        $to = WarehouseItem::firstOrCreate([
            'warehouse_id' => $toWarehouseId,
            'item_id' => $itemId,
        ], ['quantity_available' => 0]);

        // Remove (allow negative) and add
        $from->forceRemoveStock($quantity);
        $to->addStock($quantity);

        Log::debug('Stock movido', [
            'item_id' => $itemId,
            'from' => $fromWarehouseId,
            'to' => $toWarehouseId,
            'quantity' => $quantity,
        ]);
    }
}

