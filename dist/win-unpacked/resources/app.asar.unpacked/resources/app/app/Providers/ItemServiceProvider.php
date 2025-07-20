<?php

namespace App\Providers;

use App\Inventory\Item\Contracts\ItemRepositoryInterface;
use App\Inventory\Item\Repositories\ItemRepository;
use Illuminate\Support\ServiceProvider;

class ItemServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar el repositorio de items
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
