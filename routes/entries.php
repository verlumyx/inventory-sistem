<?php

use Illuminate\Support\Facades\Route;

// Entries routes
Route::prefix('entries')->name('entries.')->group(function () {
    // Main CRUD routes
    Route::get('/', App\Inventory\Entry\Controllers\ListEntriesController::class)->name('index');
    Route::get('/create', [App\Inventory\Entry\Controllers\CreateEntryController::class, 'create'])->name('create');
    Route::post('/', [App\Inventory\Entry\Controllers\CreateEntryController::class, 'store'])->name('store');
    Route::get('/{id}', App\Inventory\Entry\Controllers\GetEntryController::class)->name('show');
    Route::get('/{id}/edit', [App\Inventory\Entry\Controllers\UpdateEntryController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Inventory\Entry\Controllers\UpdateEntryController::class, 'update'])->name('update');
    Route::patch('/{id}', [App\Inventory\Entry\Controllers\UpdateEntryController::class, 'update'])->name('patch');
});

// API routes for entries
Route::prefix('api/entries')->name('api.entries.')->group(function () {
    // List and search
    Route::get('/', [App\Inventory\Entry\Controllers\ListEntriesController::class, 'api'])->name('index');
    Route::get('/search', [App\Inventory\Entry\Controllers\ListEntriesController::class, 'search'])->name('search');
    Route::get('/active', [App\Inventory\Entry\Controllers\ListEntriesController::class, 'active'])->name('active');
    Route::get('/inactive', [App\Inventory\Entry\Controllers\ListEntriesController::class, 'inactive'])->name('inactive');
    Route::get('/latest', [App\Inventory\Entry\Controllers\ListEntriesController::class, 'latest'])->name('latest');
    Route::get('/statistics', [App\Inventory\Entry\Controllers\ListEntriesController::class, 'statistics'])->name('statistics');
    
    // CRUD operations
    Route::post('/', [App\Inventory\Entry\Controllers\CreateEntryController::class, 'storeApi'])->name('store');
    Route::get('/form-data', [App\Inventory\Entry\Controllers\CreateEntryController::class, 'getFormData'])->name('form-data');
    Route::post('/validate', [App\Inventory\Entry\Controllers\CreateEntryController::class, 'validate'])->name('validate');
    
    // Individual entry operations
    Route::get('/{id}', [App\Inventory\Entry\Controllers\GetEntryController::class, 'api'])->name('show');
    Route::get('/{id}/with-items', [App\Inventory\Entry\Controllers\GetEntryController::class, 'withItems'])->name('with-items');
    Route::get('/{id}/for-display', [App\Inventory\Entry\Controllers\GetEntryController::class, 'forDisplay'])->name('for-display');
    Route::get('/{id}/exists', [App\Inventory\Entry\Controllers\GetEntryController::class, 'exists'])->name('exists');
    Route::put('/{id}', [App\Inventory\Entry\Controllers\UpdateEntryController::class, 'updateApi'])->name('update');
    Route::patch('/{id}', [App\Inventory\Entry\Controllers\UpdateEntryController::class, 'updateApi'])->name('patch');
    
    // Status operations
    Route::patch('/{id}/toggle-status', [App\Inventory\Entry\Controllers\UpdateEntryController::class, 'toggleStatus'])->name('toggle-status');
    Route::patch('/{id}/activate', [App\Inventory\Entry\Controllers\UpdateEntryController::class, 'activate'])->name('activate');
    Route::patch('/{id}/deactivate', [App\Inventory\Entry\Controllers\UpdateEntryController::class, 'deactivate'])->name('deactivate');
    
    // Search by code
    Route::get('/code/{code}', [App\Inventory\Entry\Controllers\GetEntryController::class, 'byCode'])->name('by-code');
    Route::get('/code/{code}/exists', [App\Inventory\Entry\Controllers\GetEntryController::class, 'existsByCode'])->name('code-exists');
});
