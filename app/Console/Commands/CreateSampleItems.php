<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Inventory\Item\Models\Item;

class CreateSampleItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:create-samples {count=20 : Number of items to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample items using the ItemFactory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');
        
        if ($count <= 0) {
            $this->error('❌ El número de items debe ser mayor a 0');
            return 1;
        }

        $this->info("🚀 Creando {$count} items de muestra...");
        
        // Crear items usando el factory
        $items = Item::factory()
            ->count($count)
            ->active() // Todos activos
            ->withQrCode() // Todos con código QR
            ->withDescription() // Todos con descripción
            ->create();

        $this->info("✅ Se han creado {$count} items exitosamente.");
        
        // Mostrar estadísticas
        $totalItems = Item::count();
        $activeItems = Item::where('status', true)->count();
        $itemsWithQr = Item::whereNotNull('qr_code')->count();
        $itemsWithDescription = Item::whereNotNull('description')->count();
        
        $this->newLine();
        $this->info('📊 Estadísticas del inventario:');
        $this->line("   • Total de items: {$totalItems}");
        $this->line("   • Items activos: {$activeItems}");
        $this->line("   • Items con QR: {$itemsWithQr}");
        $this->line("   • Items con descripción: {$itemsWithDescription}");
        
        // Mostrar algunos ejemplos de los items creados
        $this->newLine();
        $this->info('📦 Ejemplos de items creados:');
        
        $sampleItems = $items->take(5);
        foreach ($sampleItems as $item) {
            $price = $item->price ? '$' . number_format($item->price, 2) : 'Sin precio';
            $unit = $item->unit ?? 'Sin unidad';
            $this->line("   • {$item->code} - {$item->name}");
            $this->line("     Precio: {$price} | Unidad: {$unit} | QR: {$item->qr_code}");
        }
        
        if ($count > 5) {
            $this->line("   ... y " . ($count - 5) . " items más");
        }
        
        $this->newLine();
        $this->info('🎉 ¡Listo! Ahora puedes probar el selector de búsqueda con más items.');
        
        return 0;
    }
}
