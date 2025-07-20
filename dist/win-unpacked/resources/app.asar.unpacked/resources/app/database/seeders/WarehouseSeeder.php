<?php

namespace Database\Seeders;

use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear almacenes principales
        Warehouse::factory()->main()->create();
        Warehouse::factory()->secondary()->create();

        // Crear almacenes adicionales
        Warehouse::factory()->withName('Almacén Norte')->create([
            'description' => 'Almacén ubicado en la zona norte de la ciudad',
            'status' => true,
        ]);

        Warehouse::factory()->withName('Almacén Sur')->create([
            'description' => 'Almacén ubicado en la zona sur de la ciudad',
            'status' => true,
        ]);

        Warehouse::factory()->withName('Almacén Este')->create([
            'description' => 'Almacén ubicado en la zona este de la ciudad',
            'status' => false,
        ]);

        Warehouse::factory()->withName('Almacén Oeste')->create([
            'description' => 'Almacén ubicado en la zona oeste de la ciudad',
            'status' => true,
        ]);

        // Crear almacenes especializados
        Warehouse::factory()->withName('Almacén de Electrónicos')->create([
            'description' => 'Almacén especializado en productos electrónicos y tecnológicos',
            'status' => true,
        ]);

        Warehouse::factory()->withName('Almacén de Repuestos')->create([
            'description' => 'Almacén dedicado exclusivamente a repuestos y componentes',
            'status' => true,
        ]);

        Warehouse::factory()->withName('Almacén Temporal')->create([
            'description' => 'Almacén temporal para productos en tránsito',
            'status' => false,
        ]);

        // Crear almacén de pruebas
        Warehouse::factory()->testing()->create();

        // Crear algunos almacenes aleatorios adicionales
        Warehouse::factory()->count(5)->create();

        // Crear algunos almacenes sin descripción
        Warehouse::factory()->withoutDescription()->count(3)->create();
    }
}
