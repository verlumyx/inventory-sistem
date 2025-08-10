<?php

use App\Inventory\Adjustments\Controllers\ListAdjustmentsController;
use App\Inventory\Adjustments\Controllers\CreateAdjustmentController;
use App\Inventory\Adjustments\Controllers\UpdateAdjustmentController;
use App\Inventory\Adjustments\Controllers\GetAdjustmentController;
use App\Inventory\Adjustments\Controllers\AdjustmentStatusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Adjustment Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // List
    Route::get('/adjustments', ListAdjustmentsController::class)
        ->name('adjustments.index');

    // Create
    Route::get('/adjustments/create', [CreateAdjustmentController::class, 'create'])
        ->name('adjustments.create');
    Route::post('/adjustments', [CreateAdjustmentController::class, 'store'])
        ->name('adjustments.store');

    // Show
    Route::get('/adjustments/{adjustment}', GetAdjustmentController::class)
        ->name('adjustments.show')
        ->where('adjustment', '[0-9]+');

    // Edit/Update
    Route::get('/adjustments/{adjustment}/edit', [UpdateAdjustmentController::class, 'edit'])
        ->name('adjustments.edit')
        ->where('adjustment', '[0-9]+');
    Route::put('/adjustments/{adjustment}', [UpdateAdjustmentController::class, 'update'])
        ->name('adjustments.update')
        ->where('adjustment', '[0-9]+');
    Route::patch('/adjustments/{adjustment}', [UpdateAdjustmentController::class, 'update'])
        ->name('adjustments.patch')
        ->where('adjustment', '[0-9]+');

    // Status management
    Route::patch('/adjustments/{adjustment}/mark-as-applied', [AdjustmentStatusController::class, 'markAsApplied'])
        ->name('adjustments.mark-as-applied')
        ->where('adjustment', '[0-9]+');
});

Route::middleware(['auth:sanctum'])->prefix('api')->group(function () {
    Route::get('/adjustments', [ListAdjustmentsController::class, 'api'])
        ->name('api.adjustments.index');
});

