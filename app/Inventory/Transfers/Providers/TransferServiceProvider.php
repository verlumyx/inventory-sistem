<?php

namespace App\Inventory\Transfers\Providers;

use App\Inventory\Transfers\Contracts\TransferRepositoryInterface;
use App\Inventory\Transfers\Repositories\TransferRepository;
use Illuminate\Support\ServiceProvider;

class TransferServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TransferRepositoryInterface::class, TransferRepository::class);
    }

    public function boot(): void
    {
        //
    }
}

