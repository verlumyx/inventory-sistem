<?php

namespace App\Inventory\ExchangeRate\Providers;

use App\Inventory\ExchangeRate\Handlers\GetExchangeRateHandler;
use App\Inventory\ExchangeRate\Handlers\UpdateExchangeRateHandler;
use App\Inventory\ExchangeRate\Repositories\ExchangeRateRepository;
use Illuminate\Support\ServiceProvider;

class ExchangeRateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Repository
        $this->app->singleton(ExchangeRateRepository::class, function ($app) {
            return new ExchangeRateRepository();
        });

        // Register Handlers
        $this->app->singleton(GetExchangeRateHandler::class, function ($app) {
            return new GetExchangeRateHandler(
                $app->make(ExchangeRateRepository::class)
            );
        });

        $this->app->singleton(UpdateExchangeRateHandler::class, function ($app) {
            return new UpdateExchangeRateHandler(
                $app->make(ExchangeRateRepository::class)
            );
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
