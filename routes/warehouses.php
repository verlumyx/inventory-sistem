<?php

use App\Inventory\Warehouse\Controllers\CreateWarehouseController;
use App\Inventory\Warehouse\Controllers\GetWarehouseByCodeController;
use App\Inventory\Warehouse\Controllers\GetWarehouseController;
use App\Inventory\Warehouse\Controllers\ListActiveWarehousesController;
use App\Inventory\Warehouse\Controllers\ListWarehousesController;
use App\Inventory\Warehouse\Controllers\UpdateWarehouseController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Warehouse Web Routes
|--------------------------------------------------------------------------
|
| Rutas web para el módulo de almacenes (Warehouses) con Inertia.js
| Todas las rutas requieren autenticación
|
*/

// Listar almacenes con paginación y filtros
Route::get('/warehouses', ListWarehousesController::class)
    ->name('warehouses.index');

// Mostrar formulario de creación
Route::get('/warehouses/create', function () {
    return Inertia::render('warehouses/Create');
})->name('warehouses.create');

// Listar solo almacenes activos
Route::get('/warehouses/active', ListActiveWarehousesController::class)
    ->name('warehouses.active');

// Crear nuevo almacén
Route::post('/warehouses', CreateWarehouseController::class)
    ->name('warehouses.store');

// Obtener almacén por ID
Route::get('/warehouses/{warehouse}', GetWarehouseController::class)
    ->name('warehouses.show')
    ->where('warehouse', '[0-9]+');

// Mostrar formulario de edición
Route::get('/warehouses/{warehouse}/edit', function (int $warehouse) {
    $getWarehouseHandler = app(\App\Inventory\Warehouse\Handlers\GetWarehouseHandler::class);
    try {
        $warehouseData = $getWarehouseHandler->handleById($warehouse);
        return Inertia::render('warehouses/Edit', [
            'warehouse' => [
                'id' => $warehouseData->id,
                'code' => $warehouseData->code,
                'name' => $warehouseData->name,
                'description' => $warehouseData->description,
                'status' => $warehouseData->status,
                'status_text' => $warehouseData->status_text,
                'default' => $warehouseData->default,
                'default_text' => $warehouseData->default_text,
                'created_at' => $warehouseData->created_at->toISOString(),
                'updated_at' => $warehouseData->updated_at->toISOString(),
            ]
        ]);
    } catch (\App\Inventory\Warehouse\Exceptions\WarehouseNotFoundException $e) {
        return redirect()->route('warehouses.index')
            ->withErrors(['error' => $e->getMessage()]);
    }
})->name('warehouses.edit')
  ->where('warehouse', '[0-9]+');

// Obtener almacén por código
Route::get('/warehouses/code/{code}', GetWarehouseByCodeController::class)
    ->name('warehouses.show.by.code')
    ->where('code', 'WH-[0-9]{8}');

// Actualizar almacén
Route::put('/warehouses/{warehouse}', UpdateWarehouseController::class)
    ->name('warehouses.update')
    ->where('warehouse', '[0-9]+');

// Actualizar almacén (PATCH)
Route::patch('/warehouses/{warehouse}', UpdateWarehouseController::class)
    ->name('warehouses.patch')
    ->where('warehouse', '[0-9]+');
