<?php

/**
 * Script para crear items de muestra usando el ItemFactory
 * 
 * Uso: php scripts/create_sample_items.php [cantidad]
 * Ejemplo: php scripts/create_sample_items.php 20
 */

// Cargar el autoloader de Laravel
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Inventory\Item\Models\Item;

// Obtener la cantidad de items a crear (por defecto 20)
$count = isset($argv[1]) ? (int) $argv[1] : 20;

if ($count <= 0) {
    echo "❌ Error: El número de items debe ser mayor a 0\n";
    exit(1);
}

echo "🚀 Creando {$count} items de muestra...\n";

try {
    // Crear items usando el factory
    $items = Item::factory()
        ->count($count)
        ->active() // Todos activos
        ->withQrCode() // Todos con código QR
        ->withDescription() // Todos con descripción
        ->create();

    echo "✅ Se han creado {$count} items exitosamente.\n";
    
    // Mostrar estadísticas
    $totalItems = Item::count();
    $activeItems = Item::where('status', true)->count();
    $itemsWithQr = Item::whereNotNull('qr_code')->count();
    $itemsWithDescription = Item::whereNotNull('description')->count();
    
    echo "\n📊 Estadísticas del inventario:\n";
    echo "   • Total de items: {$totalItems}\n";
    echo "   • Items activos: {$activeItems}\n";
    echo "   • Items con QR: {$itemsWithQr}\n";
    echo "   • Items con descripción: {$itemsWithDescription}\n";
    
    // Mostrar algunos ejemplos de los items creados
    echo "\n📦 Ejemplos de items creados:\n";
    
    $sampleItems = $items->take(5);
    foreach ($sampleItems as $item) {
        $price = $item->price ? '$' . number_format($item->price, 2) : 'Sin precio';
        $unit = $item->unit ?? 'Sin unidad';
        echo "   • {$item->code} - {$item->name}\n";
        echo "     Precio: {$price} | Unidad: {$unit} | QR: {$item->qr_code}\n";
    }
    
    if ($count > 5) {
        echo "   ... y " . ($count - 5) . " items más\n";
    }
    
    echo "\n🎉 ¡Listo! Ahora puedes probar el selector de búsqueda con más items.\n";
    echo "💡 Tip: Ve a crear/editar facturas o entradas para probar la nueva funcionalidad.\n";
    
} catch (Exception $e) {
    echo "❌ Error al crear los items: " . $e->getMessage() . "\n";
    exit(1);
}
