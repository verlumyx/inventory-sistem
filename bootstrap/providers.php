<?php

return [
    App\Inventory\Warehouse\Providers\WarehouseServiceProvider::class,
    App\Inventory\Entry\Providers\EntryServiceProvider::class,
    App\Inventory\Invoice\Providers\InvoiceServiceProvider::class,
    App\Inventory\Adjustments\Providers\AdjustmentServiceProvider::class,
    App\Inventory\ExchangeRate\Providers\ExchangeRateServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\ItemServiceProvider::class,
    App\Providers\EncryptionServiceProvider::class,
    App\Inventory\Transfers\Providers\TransferServiceProvider::class,
];
