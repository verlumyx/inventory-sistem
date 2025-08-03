<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Inventory\WarehouseItem\Models\WarehouseItem;
use App\Inventory\Warehouse\Models\Warehouse;
use App\Inventory\Item\Models\Item;

class WarehouseItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener warehouses e items existentes
        $warehouses = Warehouse::all();
        $items = Item::all();

        if ($warehouses->isEmpty() || $items->isEmpty()) {
            $this->command->info('No hay warehouses o items para crear warehouse_items. Ejecuta primero los seeders de warehouses e items.');
            return;
        }

        // Crear algunas combinaciones de warehouse-item con stock
        $warehouseItemData = [
            // Almacén Principal con varios items
            [
                'warehouse_id' => $warehouses->first()->id,
                'item_id' => $items->get(0)->id ?? 1,
                'quantity_available' => 100.00,
            ],
            [
                'warehouse_id' => $warehouses->first()->id,
                'item_id' => $items->get(1)->id ?? 2,
                'quantity_available' => 50.00,
            ],
            [
                'warehouse_id' => $warehouses->first()->id,
                'item_id' => $items->get(2)->id ?? 3,
                'quantity_available' => 25.00,
            ],
        ];

        // Si hay más de un warehouse, agregar items al segundo
        if ($warehouses->count() > 1) {
            $secondWarehouse = $warehouses->get(1);
            $warehouseItemData = array_merge($warehouseItemData, [
                [
                    'warehouse_id' => $secondWarehouse->id,
                    'item_id' => $items->get(0)->id ?? 1,
                    'quantity_available' => 75.00,
                ],
                [
                    'warehouse_id' => $secondWarehouse->id,
                    'item_id' => $items->get(3)->id ?? 4,
                    'quantity_available' => 30.00,
                ],
            ]);
        }

        foreach ($warehouseItemData as $data) {
            WarehouseItem::updateOrCreate(
                [
                    'warehouse_id' => $data['warehouse_id'],
                    'item_id' => $data['item_id'],
                ],
                [
                    'quantity_available' => $data['quantity_available'],
                ]
            );
        }

        $this->command->info('WarehouseItems creados exitosamente.');
    }
}
