<?php

namespace App\Inventory\ExchangeRate\Repositories;

use App\Inventory\ExchangeRate\Models\ExchangeRate;

class ExchangeRateRepository
{
    /**
     * Get the current exchange rate.
     */
    public function getCurrent(): ExchangeRate
    {
        return ExchangeRate::current();
    }

    /**
     * Update the current exchange rate.
     */
    public function updateCurrent(float $rate): ExchangeRate
    {
        return ExchangeRate::updateCurrent($rate);
    }

    /**
     * Get the current rate value.
     */
    public function getCurrentRate(): float
    {
        return ExchangeRate::getCurrentRate();
    }

    /**
     * Check if exchange rate exists.
     */
    public function exists(): bool
    {
        return ExchangeRate::exists();
    }

    /**
     * Create initial exchange rate if it doesn't exist.
     */
    public function createInitial(): ExchangeRate
    {
        if (!$this->exists()) {
            return ExchangeRate::create(['rate' => 1.0000]);
        }
        
        return $this->getCurrent();
    }

    /**
     * Get exchange rate for display.
     */
    public function getForDisplay(): array
    {
        $exchangeRate = $this->getCurrent();
        
        return [
            'id' => $exchangeRate->id,
            'rate' => $exchangeRate->rate,
            'formatted_rate' => $exchangeRate->formatted_rate,
            'created_at' => $exchangeRate->created_at?->toISOString(),
            'updated_at' => $exchangeRate->updated_at?->toISOString(),
        ];
    }
}
