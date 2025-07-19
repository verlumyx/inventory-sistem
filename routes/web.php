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
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
