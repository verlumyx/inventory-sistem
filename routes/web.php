<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('dashboard');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Incluir rutas del módulo de almacenes
    require __DIR__.'/warehouses.php';

    // Incluir rutas del módulo de items
    require __DIR__.'/items.php';

    // Incluir rutas del módulo de entradas
    require __DIR__.'/entries.php';

    // Incluir rutas del módulo de facturas
    require __DIR__.'/invoices.php';
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
