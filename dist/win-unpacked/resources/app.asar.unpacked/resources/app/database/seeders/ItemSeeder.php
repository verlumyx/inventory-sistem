<?php

namespace Database\Seeders;

use App\Inventory\Item\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creando items de prueba...');

        // Crear items activos (mayoría)
        Item::factory()
            ->count(15)
            ->active()
            ->withQrCode()
            ->withDescription()
            ->create();

        // Crear algunos items sin Código de barra
        Item::factory()
            ->count(8)
            ->active()
            ->withoutQrCode()
            ->withDescription()
            ->create();

        // Crear algunos items sin descripción
        Item::factory()
            ->count(5)
            ->active()
            ->withQrCode()
            ->withoutDescription()
            ->create();

        // Crear items inactivos
        Item::factory()
            ->count(7)
            ->inactive()
            ->withQrCode()
            ->withDescription()
            ->create();

        // Crear algunos items específicos para demostración
        $specificItems = [
            [
                'name' => 'MacBook Pro 16" M2',
                'description' => 'Laptop profesional Apple con chip M2, 16GB RAM, 512GB SSD. Ideal para desarrollo y diseño.',
                'qr_code' => 'QR-MACBOOK-001',
                'price' => 2499.99,
                'unit' => 'pcs',
                'status' => true,
            ],
            [
                'name' => 'Monitor Dell UltraSharp 27"',
                'description' => 'Monitor profesional 4K con calibración de color precisa para diseño gráfico.',
                'qr_code' => 'QR-MONITOR-001',
                'price' => 549.99,
                'unit' => 'pcs',
                'status' => true,
            ],
            [
                'name' => 'Teclado Mecánico Logitech MX',
                'description' => 'Teclado mecánico inalámbrico con retroiluminación y teclas programables.',
                'qr_code' => 'QR-KEYBOARD-001',
                'price' => 129.99,
                'unit' => 'pcs',
                'status' => true,
            ],
            [
                'name' => 'Impresora HP LaserJet Pro',
                'description' => 'Impresora láser monocromática de alta velocidad para oficina.',
                'qr_code' => null,
                'price' => 299.99,
                'unit' => 'pcs',
                'status' => true,
            ],
            [
                'name' => 'Silla Ergonómica Herman Miller',
                'description' => 'Silla de oficina ergonómica con soporte lumbar ajustable y reposabrazos.',
                'qr_code' => 'QR-CHAIR-001',
                'price' => 1299.99,
                'unit' => 'pcs',
                'status' => true,
            ],
            [
                'name' => 'Tablet Samsung Galaxy Tab S8',
                'description' => 'Tablet Android con S Pen incluido, ideal para toma de notas y presentaciones.',
                'qr_code' => 'QR-TABLET-001',
                'price' => 699.99,
                'unit' => 'pcs',
                'status' => false, // Inactivo para demostrar filtros
            ],
        ];

        foreach ($specificItems as $itemData) {
            Item::create($itemData);
        }

        $totalItems = Item::count();
        $activeItems = Item::where('status', true)->count();
        $inactiveItems = Item::where('status', false)->count();

        $this->command->info("✅ Se crearon {$totalItems} items:");
        $this->command->info("   - {$activeItems} activos");
        $this->command->info("   - {$inactiveItems} inactivos");
        $this->command->info('');
    }
}
