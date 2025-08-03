<?php

namespace App\Inventory\ExchangeRate\Handlers;

use App\Inventory\ExchangeRate\Models\ExchangeRate;
use App\Inventory\ExchangeRate\Repositories\ExchangeRateRepository;
use Illuminate\Support\Facades\Log;

class GetExchangeRateHandler
{
    public function __construct(
        private ExchangeRateRepository $exchangeRateRepository
    ) {}

    /**
     * Get the current exchange rate.
     */
    public function handle(): ExchangeRate
    {
        try {
            Log::info('Obteniendo tasa de cambio actual');

            $exchangeRate = $this->exchangeRateRepository->getCurrent();

            Log::info('Tasa de cambio obtenida exitosamente', [
                'rate' => $exchangeRate->rate,
            ]);

            return $exchangeRate;

        } catch (\Exception $e) {
            Log::error('Error al obtener tasa de cambio', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get exchange rate for display.
     */
    public function handleForDisplay(): array
    {
        try {
            Log::info('Obteniendo tasa de cambio para mostrar');

            $displayData = $this->exchangeRateRepository->getForDisplay();

            Log::info('Datos de tasa de cambio preparados para mostrar', [
                'rate' => $displayData['rate'],
            ]);

            return $displayData;

        } catch (\Exception $e) {
            Log::error('Error al preparar datos de tasa de cambio para mostrar', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get current rate value.
     */
    public function getCurrentRate(): float
    {
        try {
            return $this->exchangeRateRepository->getCurrentRate();
        } catch (\Exception $e) {
            Log::error('Error al obtener valor de tasa de cambio', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
