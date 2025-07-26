<?php

namespace App\Inventory\Invoice\Handlers;

use App\Inventory\Invoice\Models\Invoice;
use App\Inventory\WarehouseItem\Models\WarehouseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceInventoryHandler
{
    /**
     * Handle inventory update when invoice is marked as paid.
     * Reduces stock from warehouse.
     */
    public function handlePaidInventoryUpdate(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            Log::info('Actualizando inventario por factura pagada', [
                'invoice_id' => $invoice->id,
                'invoice_code' => $invoice->code,
                'warehouse_id' => $invoice->warehouse_id,
            ]);

            foreach ($invoice->invoiceItems as $invoiceItem) {
                $this->reduceWarehouseStock(
                    $invoice->warehouse_id,
                    $invoiceItem->item_id,
                    $invoiceItem->amount
                );
            }

            Log::info('Inventario actualizado exitosamente por factura pagada', [
                'invoice_id' => $invoice->id,
                'items_processed' => $invoice->invoiceItems->count(),
            ]);
        });
    }

    /**
     * Handle inventory update when invoice is marked as pending.
     * Returns stock to warehouse.
     */
    public function handlePendingInventoryUpdate(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            Log::info('Devolviendo inventario por factura marcada como pendiente', [
                'invoice_id' => $invoice->id,
                'invoice_code' => $invoice->code,
                'warehouse_id' => $invoice->warehouse_id,
            ]);

            foreach ($invoice->invoiceItems as $invoiceItem) {
                $this->addWarehouseStock(
                    $invoice->warehouse_id,
                    $invoiceItem->item_id,
                    $invoiceItem->amount
                );
            }

            Log::info('Inventario devuelto exitosamente por factura pendiente', [
                'invoice_id' => $invoice->id,
                'items_processed' => $invoice->invoiceItems->count(),
            ]);
        });
    }

    /**
     * Reduce stock from warehouse when invoice is paid.
     */
    private function reduceWarehouseStock(int $warehouseId, int $itemId, float $quantity): void
    {
        try {
            Log::info('Reduciendo stock en warehouse', [
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'quantity' => $quantity,
            ]);

            // Buscar o crear el registro de warehouse_item
            $warehouseItem = WarehouseItem::firstOrCreate(
                [
                    'warehouse_id' => $warehouseId,
                    'item_id' => $itemId,
                ],
                [
                    'quantity_available' => 0,
                ]
            );

            // Verificar si hay suficiente stock
            if (!$warehouseItem->hasEnoughStock($quantity)) {
                Log::warning('Stock insuficiente para reducir, procediendo con stock negativo', [
                    'warehouse_id' => $warehouseId,
                    'item_id' => $itemId,
                    'requested_quantity' => $quantity,
                    'available_quantity' => $warehouseItem->quantity_available,
                ]);
            }

            // Reducir el stock (permite stock negativo)
            $warehouseItem->forceRemoveStock($quantity);

            Log::info('Stock reducido exitosamente', [
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'quantity_reduced' => $quantity,
                'new_quantity' => $warehouseItem->fresh()->quantity_available,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al reducir stock en warehouse', [
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Add stock to warehouse when invoice is marked as pending.
     */
    private function addWarehouseStock(int $warehouseId, int $itemId, float $quantity): void
    {
        try {
            Log::info('Agregando stock en warehouse', [
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'quantity' => $quantity,
            ]);

            // Buscar o crear el registro de warehouse_item
            $warehouseItem = WarehouseItem::firstOrCreate(
                [
                    'warehouse_id' => $warehouseId,
                    'item_id' => $itemId,
                ],
                [
                    'quantity_available' => 0,
                ]
            );

            // Agregar el stock
            $warehouseItem->addStock($quantity);

            Log::info('Stock agregado exitosamente', [
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'quantity_added' => $quantity,
                'new_quantity' => $warehouseItem->fresh()->quantity_available,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al agregar stock en warehouse', [
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
