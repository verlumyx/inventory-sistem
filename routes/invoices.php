<?php

use App\Inventory\Invoice\Controllers\ListInvoicesController;
use App\Inventory\Invoice\Controllers\CreateInvoiceController;
use App\Inventory\Invoice\Controllers\UpdateInvoiceController;
use App\Inventory\Invoice\Controllers\GetInvoiceController;
use App\Http\Controllers\Inventory\Invoice\Controllers\InvoiceStatusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Invoice Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Invoice module. These routes handle
| all CRUD operations for invoices including listing, creating,
| updating, and viewing invoice details.
|
*/

// Invoice web routes
Route::middleware(['auth'])->group(function () {
    
    // List invoices
    Route::get('/invoices', ListInvoicesController::class)
        ->name('invoices.index');
    
    // Create invoice
    Route::get('/invoices/create', [CreateInvoiceController::class, 'create'])
        ->name('invoices.create');
    
    Route::post('/invoices', [CreateInvoiceController::class, 'store'])
        ->name('invoices.store');
    
    // Show invoice
    Route::get('/invoices/{invoice}', GetInvoiceController::class)
        ->name('invoices.show')
        ->where('invoice', '[0-9]+');
    
    // Edit invoice
    Route::get('/invoices/{invoice}/edit', [UpdateInvoiceController::class, 'edit'])
        ->name('invoices.edit')
        ->where('invoice', '[0-9]+');
    
    Route::put('/invoices/{invoice}', [UpdateInvoiceController::class, 'update'])
        ->name('invoices.update')
        ->where('invoice', '[0-9]+');
    
    Route::patch('/invoices/{invoice}', [UpdateInvoiceController::class, 'update'])
        ->name('invoices.patch')
        ->where('invoice', '[0-9]+');

    // Invoice status routes
    Route::patch('/invoices/{invoice}/mark-as-paid', [InvoiceStatusController::class, 'markAsPaid'])
        ->name('invoices.mark-as-paid')
        ->where('invoice', '[0-9]+');

    Route::patch('/invoices/{invoice}/mark-as-pending', [InvoiceStatusController::class, 'markAsPending'])
        ->name('invoices.mark-as-pending')
        ->where('invoice', '[0-9]+');

    // Invoice print route
    Route::post('/invoices/{invoice}/print', [InvoiceStatusController::class, 'print'])
        ->name('invoices.print')
        ->where('invoice', '[0-9]+');

    // Rutas para PDF térmico 58mm
    // Vista previa PDF visual para navegador
    Route::get('/invoices/{invoice}/pdf/thermal/preview', [InvoiceStatusController::class, 'generateThermalPdfPreview'])
        ->name('invoices.pdf.thermal.preview')
        ->where('invoice', '[0-9]+');

    // Impresión directa con comandos ESC/POS para impresora térmica
    Route::get('/invoices/{invoice}/pdf/thermal/print', [InvoiceStatusController::class, 'generateThermalPrint'])
        ->name('invoices.pdf.thermal.print')
        ->where('invoice', '[0-9]+');
});

// Invoice API routes
Route::middleware(['auth:sanctum'])->prefix('api')->group(function () {
    
    // List invoices API
    Route::get('/invoices', [ListInvoicesController::class, 'api'])
        ->name('api.invoices.index');
    
    // Search invoices API
    Route::get('/invoices/search', [ListInvoicesController::class, 'api'])
        ->name('api.invoices.search');
});
