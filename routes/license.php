<?php

use App\Http\Controllers\LicenseController;
use Illuminate\Support\Facades\Route;

// Rutas de licencia (no requieren middleware de licencia)
Route::name('license.')->group(function () {
    Route::get('/license/renewal', [LicenseController::class, 'renewal'])->name('renewal');
    Route::post('/license/generate', [LicenseController::class, 'generateCode'])->name('generate');
    Route::post('/license/activate', [LicenseController::class, 'activate'])->name('activate');
});
