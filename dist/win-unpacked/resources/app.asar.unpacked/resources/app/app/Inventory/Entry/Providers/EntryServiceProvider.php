<?php

namespace App\Inventory\Entry\Providers;

use App\Inventory\Entry\Contracts\EntryRepositoryInterface;
use App\Inventory\Entry\Repositories\EntryRepository;
use Illuminate\Support\ServiceProvider;

class EntryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interface to implementation
        $this->app->bind(EntryRepositoryInterface::class, EntryRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
