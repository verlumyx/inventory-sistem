<?php

use Illuminate\Support\Facades\Route;
use App\Inventory\Item\Controllers\ListItemsController;
use App\Inventory\Item\Controllers\CreateItemController;
use App\Inventory\Item\Controllers\UpdateItemController;
use App\Inventory\Item\Controllers\GetItemController;

/*
|--------------------------------------------------------------------------
| Items Routes
|--------------------------------------------------------------------------
|
| Aquí se definen todas las rutas para el módulo de Items.
| Todas las rutas están protegidas por el middleware 'auth'.
|
*/

Route::prefix('items')->name('items.')->group(function () {
    
    // Listar items (GET /items)
    Route::get('/', ListItemsController::class)->name('index');
    
    // Mostrar formulario de creación (GET /items/create)
    Route::get('/create', [CreateItemController::class, 'create'])->name('create');
    
    // Crear nuevo item (POST /items)
    Route::post('/', [CreateItemController::class, 'store'])->name('store');
    
    // Mostrar item específico (GET /items/{id})
    Route::get('/{id}', GetItemController::class)->name('show')
        ->where('id', '[0-9]+');
    
    // Mostrar formulario de edición (GET /items/{id}/edit)
    Route::get('/{id}/edit', [UpdateItemController::class, 'edit'])->name('edit')
        ->where('id', '[0-9]+');
    
    // Actualizar item (PUT/PATCH /items/{id})
    Route::put('/{id}', [UpdateItemController::class, 'update'])->name('update')
        ->where('id', '[0-9]+');
    Route::patch('/{id}', [UpdateItemController::class, 'update'])->name('patch')
        ->where('id', '[0-9]+');
    

    
});

/*
|--------------------------------------------------------------------------
| API Routes para Items (opcional)
|--------------------------------------------------------------------------
|
| Rutas adicionales para API si se necesitan en el futuro
|
*/

Route::prefix('api/items')->name('api.items.')->group(function () {
    
    // Buscar items por código
    Route::get('/search/code/{code}', function (string $code) {
        $handler = app(\App\Inventory\Item\Handlers\GetItemHandler::class);
        try {
            $item = $handler->handleByCode($code);
            return response()->json($item->toApiArray());
        } catch (\App\Inventory\Item\Exceptions\ItemNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    })->name('search.code');
    
    // Buscar items por Código de barra
    Route::get('/search/qr/{qrCode}', function (string $qrCode) {
        $handler = app(\App\Inventory\Item\Handlers\GetItemHandler::class);
        try {
            $item = $handler->handleByQrCode($qrCode);
            return response()->json($item->toApiArray());
        } catch (\App\Inventory\Item\Exceptions\ItemNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    })->name('search.qr');
    
    // Obtener estadísticas de items
    Route::get('/statistics', function () {
        $handler = app(\App\Inventory\Item\Handlers\ListItemsHandler::class);
        $statistics = $handler->handleStatistics();
        return response()->json($statistics);
    })->name('statistics');
    
    // Verificar si un código es único
    Route::get('/check/code/{code}', function (string $code) {
        $repository = app(\App\Inventory\Item\Contracts\ItemRepositoryInterface::class);
        $isUnique = $repository->isCodeUnique($code);
        return response()->json(['unique' => $isUnique]);
    })->name('check.code');
    
    // Verificar si un Código de barra es único
    Route::get('/check/qr/{qrCode}', function (string $qrCode) {
        $repository = app(\App\Inventory\Item\Contracts\ItemRepositoryInterface::class);
        $isUnique = $repository->isQrCodeUnique($qrCode);
        return response()->json(['unique' => $isUnique]);
    })->name('check.qr');
    
});
