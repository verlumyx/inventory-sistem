<?php

namespace App\Inventory\Invoice\Services;

use App\Inventory\Invoice\Models\Invoice;
use App\Inventory\Invoice\Exceptions\InsufficientStockException;
use App\Inventory\WarehouseItem\Models\WarehouseItem;
use Illuminate\Support\Facades\Log;

class InvoiceStockValidator
{
    /**
     * Validate that there's enough stock for all items in the invoice.
     *
     * @param Invoice $invoice
     * @throws InsufficientStockException
     */
    public function validateStockForPayment(Invoice $invoice): void
    {
        Log::info('Validating stock for invoice payment', [
            'invoice_id' => $invoice->id,
            'invoice_code' => $invoice->code,
            'warehouse_id' => $invoice->warehouse_id,
        ]);

        $stockErrors = [];

        foreach ($invoice->invoiceItems as $invoiceItem) {
            $warehouseItem = WarehouseItem::where('warehouse_id', $invoice->warehouse_id)
                ->where('item_id', $invoiceItem->item_id)
                ->first();

            $availableStock = $warehouseItem ? $warehouseItem->quantity_available : 0;
            $requestedQuantity = $invoiceItem->amount;

            Log::debug('Checking stock for item', [
                'item_id' => $invoiceItem->item_id,
                'item_name' => $invoiceItem->item->display_name ?? 'Unknown',
                'requested' => $requestedQuantity,
                'available' => $availableStock,
            ]);

            if ($availableStock < $requestedQuantity) {
                $stockErrors[] = [
                    'item_id' => $invoiceItem->item_id,
                    'item_name' => $invoiceItem->item->display_name ?? 'Item ID: ' . $invoiceItem->item_id,
                    'requested' => number_format($requestedQuantity, 2),
                    'available' => number_format($availableStock, 2),
                ];
            }
        }

        if (!empty($stockErrors)) {
            Log::warning('Insufficient stock detected for invoice payment', [
                'invoice_id' => $invoice->id,
                'stock_errors' => $stockErrors,
            ]);

            throw new InsufficientStockException($stockErrors);
        }

        Log::info('Stock validation passed for invoice payment', [
            'invoice_id' => $invoice->id,
            'items_validated' => $invoice->invoiceItems->count(),
        ]);
    }

    /**
     * Get stock information for all items in the invoice.
     *
     * @param Invoice $invoice
     * @return array
     */
    public function getStockInformation(Invoice $invoice): array
    {
        $stockInfo = [];

        foreach ($invoice->invoiceItems as $invoiceItem) {
            $warehouseItem = WarehouseItem::where('warehouse_id', $invoice->warehouse_id)
                ->where('item_id', $invoiceItem->item_id)
                ->first();

            $availableStock = $warehouseItem ? $warehouseItem->quantity_available : 0;
            $requestedQuantity = $invoiceItem->amount;

            $stockInfo[] = [
                'item_id' => $invoiceItem->item_id,
                'item_name' => $invoiceItem->item->display_name ?? 'Item ID: ' . $invoiceItem->item_id,
                'requested' => $requestedQuantity,
                'available' => $availableStock,
                'sufficient' => $availableStock >= $requestedQuantity,
                'shortage' => max(0, $requestedQuantity - $availableStock),
            ];
        }

        return $stockInfo;
    }
}
