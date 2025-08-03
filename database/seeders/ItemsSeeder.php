<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Inventory\Item\Models\Item;

class ItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear 20 items usando el factory
        Item::factory()
            ->count(20)
            ->active() // Todos activos
            ->withQrCode() // Todos con código QR
            ->withDescription() // Todos con descripción
            ->create();

        $this->command->info('✅ Se han creado 20 items exitosamente.');
        
        // Mostrar algunos ejemplos
        $items = Item::latest()->take(5)->get();
        $this->command->info('📦 Últimos 5 items creados:');
        
        foreach ($items as $item) {
            $this->command->line("   • {$item->code} - {$item->name} - \${$item->price} - {$item->unit}");
        }
    }
}
