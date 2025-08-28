<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('dashboard');
})->name('home');

// Incluir rutas de licencia (sin middleware de auth para permitir acceso cuando la licencia expira)
require __DIR__.'/license.php';

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Incluir rutas del módulo de almacenes
    require __DIR__.'/warehouses.php';

    // Incluir rutas del módulo de items
    require __DIR__.'/items.php';

    // Incluir rutas del módulo de entradas
    require __DIR__.'/entries.php';

    // Incluir rutas del módulo de facturas
    require __DIR__.'/invoices.php';

    // Incluir rutas del módulo de ajustes
    require __DIR__.'/adjustments.php';

    // Incluir rutas del módulo de traslados
    require __DIR__.'/transfers.php';
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
