<?php

namespace App\Inventory\Invoice\Providers;

use App\Inventory\Invoice\Contracts\InvoiceRepositoryInterface;
use App\Inventory\Invoice\Repositories\InvoiceRepository;
use App\Inventory\Invoice\Services\InvoiceStockValidator;
use Illuminate\Support\ServiceProvider;

class InvoiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind the repository interface to its implementation
        $this->app->bind(
            InvoiceRepositoryInterface::class,
            InvoiceRepository::class
        );

        // Register the stock validator service
        $this->app->singleton(InvoiceStockValidator::class, function ($app) {
            return new InvoiceStockValidator();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
