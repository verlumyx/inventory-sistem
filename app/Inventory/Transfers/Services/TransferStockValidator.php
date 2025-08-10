<?php

namespace App\Inventory\Transfers\Services;

use App\Inventory\Transfers\Models\Transfer;
use App\Inventory\Transfers\Exceptions\InsufficientStockException;
use App\Inventory\WarehouseItem\Models\WarehouseItem;
use Illuminate\Support\Facades\Log;

class TransferStockValidator
{
    /**
     * Validate that origin warehouse has enough stock for all items in the transfer.
     * @throws InsufficientStockException
     */
    public function validateForCompletion(Transfer $transfer): void
    {
        Log::info('Validando stock para completar traslado', [
            'transfer_id' => $transfer->id,
            'code' => $transfer->code,
            'warehouse_source_id' => $transfer->warehouse_source_id,
        ]);

        $stockErrors = [];

        foreach ($transfer->items as $tItem) {
            $warehouseItem = WarehouseItem::where('warehouse_id', $transfer->warehouse_source_id)
                ->where('item_id', $tItem->item_id)
                ->first();

            $available = $warehouseItem ? (float)$warehouseItem->quantity_available : 0.0;
            $requested = (float)$tItem->amount;

            Log::debug('Checking stock for transfer item', [
                'item_id' => $tItem->item_id,
                'requested' => $requested,
                'available' => $available,
            ]);

            if ($available < $requested) {
                $stockErrors[] = [
                    'item_id' => $tItem->item_id,
                    'item_name' => $tItem->item->display_name ?? ('Item ID: '.$tItem->item_id),
                    'requested' => number_format($requested, 2),
                    'available' => number_format($available, 2),
                ];
            }
        }

        if (!empty($stockErrors)) {
            Log::warning('Insufficient stock for transfer completion', [
                'transfer_id' => $transfer->id,
                'stock_errors' => $stockErrors,
            ]);
            throw new InsufficientStockException($stockErrors);
        }

        Log::info('Stock validation passed for transfer completion', [
            'transfer_id' => $transfer->id,
        ]);
    }
}

