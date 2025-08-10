<?php

use App\Inventory\Transfers\Controllers\ListTransfersController;
use App\Inventory\Transfers\Controllers\CreateTransferController;
use App\Inventory\Transfers\Controllers\GetTransferController;
use App\Inventory\Transfers\Controllers\UpdateTransferController;
use App\Inventory\Transfers\Controllers\ApproveTransferController;
use Illuminate\Support\Facades\Route;

// Transfers routes
Route::prefix('transfers')->name('transfers.')->group(function () {
    Route::get('/', ListTransfersController::class)->name('index');
    Route::get('/create', [CreateTransferController::class, 'create'])->name('create');
    Route::post('/', [CreateTransferController::class, 'store'])->name('store');
    Route::get('/{transfer}', GetTransferController::class)->name('show')->where('transfer', '[0-9]+');
    Route::get('/{transfer}/edit', [UpdateTransferController::class, 'edit'])->name('edit')->where('transfer', '[0-9]+');
    Route::put('/{transfer}', [UpdateTransferController::class, 'update'])->name('update')->where('transfer', '[0-9]+');
    Route::patch('/{transfer}', [UpdateTransferController::class, 'update'])->name('patch')->where('transfer', '[0-9]+');
    Route::post('/{transfer}/approve', ApproveTransferController::class)->name('approve')->where('transfer', '[0-9]+');
});

