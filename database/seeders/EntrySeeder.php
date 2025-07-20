<?php

namespace Database\Seeders;

use App\Inventory\Entry\Models\Entry;
use App\Inventory\Entry\Models\EntryItem;
use App\Inventory\Item\Models\Item;
use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creando entradas de prueba...');

        // Verificar que existan items y almacenes
        $itemsCount = Item::count();
        $warehousesCount = Warehouse::count();

        if ($itemsCount === 0) {
            $this->command->warn('No hay items disponibles. Ejecute ItemSeeder primero.');
            return;
        }

        if ($warehousesCount === 0) {
            $this->command->warn('No hay almacenes disponibles. Ejecute WarehouseSeeder primero.');
            return;
        }

        // Crear algunas entradas específicas para demostración
        $specificEntries = [
            [
                'name' => 'Compra de Equipos de Oficina - Enero 2025',
                'description' => 'Entrada de equipos de oficina para el nuevo año. Incluye laptops, monitores y periféricos para el equipo de desarrollo.',
                'status' => 0, // Por recibir
                'items' => [
                    ['item_name_contains' => 'MacBook', 'amount' => 5],
                    ['item_name_contains' => 'Monitor', 'amount' => 10],
                    ['item_name_contains' => 'Teclado', 'amount' => 15],
                ]
            ],
            [
                'name' => 'Recepción de Inventario - Diciembre 2024',
                'description' => 'Entrada de inventario de fin de año. Equipos de reemplazo y nuevas adquisiciones.',
                'status' => 1, // Recibido
                'items' => [
                    ['item_name_contains' => 'Impresora', 'amount' => 3],
                    ['item_name_contains' => 'Silla', 'amount' => 20],
                    ['item_name_contains' => 'Tablet', 'amount' => 8],
                ]
            ],
            [
                'name' => 'Transferencia de Almacén Central',
                'description' => 'Transferencia de equipos desde almacén central hacia sucursales.',
                'status' => 0, // Por recibir
                'items' => [
                    ['item_name_contains' => 'Laptop', 'amount' => 12],
                    ['item_name_contains' => 'Mouse', 'amount' => 25],
                ]
            ],
            [
                'name' => 'Entrada de Emergencia - Soporte Técnico',
                'description' => 'Entrada de emergencia para cubrir necesidades críticas del departamento de soporte técnico.',
                'status' => 1, // Recibido
                'items' => [
                    ['item_name_contains' => 'Servidor', 'amount' => 2],
                    ['item_name_contains' => 'Router', 'amount' => 5],
                ]
            ],
            [
                'name' => 'Reposición de Stock - Noviembre 2024',
                'description' => 'Reposición programada de stock según plan de inventario mensual.',
                'status' => false, // Inactiva para demostrar filtros
                'items' => [
                    ['item_name_contains' => 'Calculadora', 'amount' => 30],
                    ['item_name_contains' => 'Lámpara', 'amount' => 15],
                ]
            ],
        ];

        foreach ($specificEntries as $entryData) {
            $this->createEntryWithItems($entryData);
        }

        // Crear entradas adicionales usando el factory
        $this->command->info('Creando entradas adicionales con factory...');

        // Entradas activas recientes
        Entry::factory()
            ->count(15)
            ->active()
            ->recent()
            ->create()
            ->each(function ($entry) {
                $this->addRandomItemsToEntry($entry);
            });

        // Entradas activas antiguas
        Entry::factory()
            ->count(10)
            ->active()
            ->old()
            ->create()
            ->each(function ($entry) {
                $this->addRandomItemsToEntry($entry);
            });

        // Entradas inactivas
        Entry::factory()
            ->count(5)
            ->inactive()
            ->create()
            ->each(function ($entry) {
                $this->addRandomItemsToEntry($entry);
            });

        $totalEntries = Entry::count();
        $this->command->info("✅ Se crearon {$totalEntries} entradas exitosamente.");
    }

    /**
     * Create an entry with specific items.
     */
    private function createEntryWithItems(array $entryData): void
    {
        DB::transaction(function () use ($entryData) {
            // Crear la entrada
            $entry = Entry::create([
                'name' => $entryData['name'],
                'description' => $entryData['description'],
                'status' => $entryData['status'],
            ]);

            // Agregar items a la entrada
            foreach ($entryData['items'] as $itemData) {
                $item = Item::where('name', 'like', '%' . $itemData['item_name_contains'] . '%')
                    ->first();

                if ($item) {
                    $warehouse = Warehouse::inRandomOrder()->first();

                    if ($warehouse) {
                        EntryItem::create([
                            'entry_id' => $entry->id,
                            'item_id' => $item->id,
                            'warehouse_id' => $warehouse->id,
                            'amount' => $itemData['amount'],
                        ]);
                    }
                }
            }

            $this->command->info("✓ Entrada creada: {$entry->name} ({$entry->code})");
        });
    }

    /**
     * Add random items to an entry.
     */
    private function addRandomItemsToEntry(Entry $entry): void
    {
        $itemsCount = rand(1, 5); // Entre 1 y 5 items por entrada
        $items = Item::inRandomOrder()->limit($itemsCount)->get();
        $warehouses = Warehouse::all();

        foreach ($items as $item) {
            $warehouse = $warehouses->random();
            $amount = rand(1, 50); // Cantidad aleatoria entre 1 y 50

            EntryItem::create([
                'entry_id' => $entry->id,
                'item_id' => $item->id,
                'warehouse_id' => $warehouse->id,
                'amount' => $amount,
            ]);
        }
    }
}
