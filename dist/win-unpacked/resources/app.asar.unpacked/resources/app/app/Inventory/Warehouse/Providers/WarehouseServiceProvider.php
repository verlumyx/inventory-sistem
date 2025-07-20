<?php

namespace App\Inventory\Warehouse\Providers;

use App\Inventory\Warehouse\Contracts\WarehouseRepositoryInterface;
use App\Inventory\Warehouse\Repositories\WarehouseRepository;
use Illuminate\Support\ServiceProvider;

class WarehouseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar el binding del repository
        $this->app->bind(
            WarehouseRepositoryInterface::class,
            WarehouseRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
