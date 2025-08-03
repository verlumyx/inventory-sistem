<?php

namespace App\Inventory\ExchangeRate\Handlers;

use App\Inventory\ExchangeRate\Models\ExchangeRate;
use App\Inventory\ExchangeRate\Repositories\ExchangeRateRepository;
use Illuminate\Support\Facades\Log;

class UpdateExchangeRateHandler
{
    public function __construct(
        private ExchangeRateRepository $exchangeRateRepository
    ) {}

    /**
     * Update the exchange rate.
     */
    public function handle(float $rate): ExchangeRate
    {
        try {
            Log::info('Actualizando tasa de cambio', [
                'new_rate' => $rate,
            ]);

            // Validar que la tasa sea positiva
            if ($rate <= 0) {
                throw new \InvalidArgumentException('La tasa de cambio debe ser mayor a 0');
            }

            $exchangeRate = $this->exchangeRateRepository->updateCurrent($rate);

            Log::info('Tasa de cambio actualizada exitosamente', [
                'id' => $exchangeRate->id,
                'old_rate' => $exchangeRate->getOriginal('rate'),
                'new_rate' => $exchangeRate->rate,
            ]);

            return $exchangeRate;

        } catch (\Exception $e) {
            Log::error('Error al actualizar tasa de cambio', [
                'rate' => $rate,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Initialize exchange rate if it doesn't exist.
     */
    public function initialize(): ExchangeRate
    {
        try {
            Log::info('Inicializando tasa de cambio');

            $exchangeRate = $this->exchangeRateRepository->createInitial();

            Log::info('Tasa de cambio inicializada', [
                'id' => $exchangeRate->id,
                'rate' => $exchangeRate->rate,
            ]);

            return $exchangeRate;

        } catch (\Exception $e) {
            Log::error('Error al inicializar tasa de cambio', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
