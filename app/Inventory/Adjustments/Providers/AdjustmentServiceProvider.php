<?php

namespace App\Inventory\Adjustments\Providers;

use App\Inventory\Adjustments\Contracts\AdjustmentRepositoryInterface;
use App\Inventory\Adjustments\Repositories\AdjustmentRepository;
use App\Inventory\Adjustments\Services\AdjustmentInventoryService;
use Illuminate\Support\ServiceProvider;

class AdjustmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AdjustmentRepositoryInterface::class,
            AdjustmentRepository::class
        );

        $this->app->singleton(AdjustmentInventoryService::class);
    }

    public function boot(): void
    {
        //
    }
}

