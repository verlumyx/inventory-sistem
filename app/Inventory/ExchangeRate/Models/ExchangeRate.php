<?php

namespace App\Inventory\ExchangeRate\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exchange_rates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rate',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate' => 'decimal:4',
    ];

    /**
     * Get the current exchange rate.
     * Since we only have one record, we get the first one.
     */
    public static function current(): self
    {
        return static::first() ?? static::create(['rate' => 1.0000]);
    }

    /**
     * Update the current exchange rate.
     */
    public static function updateCurrent(float $rate): self
    {
        $exchangeRate = static::current();
        $exchangeRate->update(['rate' => $rate]);
        return $exchangeRate;
    }

    /**
     * Get the current rate value.
     */
    public static function getCurrentRate(): float
    {
        return static::current()->rate;
    }

    /**
     * Convert amount using current exchange rate.
     */
    public static function convert(float $amount): float
    {
        return $amount * static::getCurrentRate();
    }

    /**
     * Get formatted rate for display.
     */
    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate, 4);
    }
}
