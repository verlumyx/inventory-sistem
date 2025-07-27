<?php

namespace Database\Seeders;

use App\Inventory\Invoice\Models\Invoice;
use App\Inventory\Invoice\Models\InvoiceItem;
use App\Inventory\Warehouse\Models\Warehouse;
use App\Inventory\Item\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creando facturas de ejemplo...');

        // Obtener warehouses e items existentes
        $warehouses = Warehouse::active()->get();
        $items = Item::active()->get();

        if ($warehouses->isEmpty() || $items->isEmpty()) {
            $this->command->warn('No hay warehouses o items activos. Saltando seeder de facturas.');
            return;
        }

        // Crear facturas específicas con items
        $this->createSpecificInvoices($warehouses, $items);

        // Crear facturas aleatorias
        $this->createRandomInvoices($warehouses, $items);

        $this->command->info('Facturas creadas exitosamente.');
    }

    /**
     * Create specific invoices with predefined items.
     */
    private function createSpecificInvoices($warehouses, $items): void
    {
        $specificInvoices = [
            [
                'warehouse' => $warehouses->first(),
                'items' => [
                    ['item' => $items->first(), 'amount' => 5, 'price' => 150.00],
                    ['item' => $items->skip(1)->first(), 'amount' => 2, 'price' => 75.50],
                ],
            ],
            [
                'warehouse' => $warehouses->skip(1)->first() ?? $warehouses->first(),
                'items' => [
                    ['item' => $items->skip(2)->first() ?? $items->first(), 'amount' => 10, 'price' => 25.00],
                ],
            ],
        ];

        foreach ($specificInvoices as $invoiceData) {
            $this->createInvoiceWithItems($invoiceData['warehouse'], $invoiceData['items']);
        }
    }

    /**
     * Create random invoices.
     */
    private function createRandomInvoices($warehouses, $items): void
    {
        for ($i = 0; $i < 8; $i++) {
            $warehouse = $warehouses->random();
            $itemCount = rand(1, 5);
            $invoiceItems = [];

            $selectedItems = $items->random($itemCount);

            foreach ($selectedItems as $item) {
                $invoiceItems[] = [
                    'item' => $item,
                    'amount' => rand(1, 20),
                    'price' => round(rand(10, 500) + (rand(0, 99) / 100), 2),
                ];
            }

            $this->createInvoiceWithItems($warehouse, $invoiceItems);
        }
    }

    /**
     * Create an invoice with specific items.
     */
    private function createInvoiceWithItems($warehouse, $items): void
    {
        DB::transaction(function () use ($warehouse, $items) {
            // Crear la factura con status aleatorio
            $invoice = Invoice::create([
                'warehouse_id' => $warehouse->id,
                'status' => rand(0, 1), // 0 = Por pagar, 1 = Pagada
            ]);

            // Agregar items a la factura
            foreach ($items as $itemData) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $itemData['item']->id,
                    'amount' => $itemData['amount'],
                    'price' => $itemData['price'],
                ]);
            }

            $this->command->info("Factura {$invoice->code} creada con " . count($items) . " items.");
        });
    }
}
