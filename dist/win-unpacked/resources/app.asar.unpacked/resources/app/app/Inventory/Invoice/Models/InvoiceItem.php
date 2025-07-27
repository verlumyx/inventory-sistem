<?php

namespace App\Inventory\Invoice\Models;

use App\Inventory\Item\Models\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'invoice_items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'invoice_id',
        'item_id',
        'amount',
        'price',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [];

    /**
     * Get the invoice that owns the invoice item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the item that belongs to the invoice item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the subtotal attribute.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->amount * $this->price;
    }

    /**
     * Get formatted amount with unit.
     */
    public function getFormattedAmountAttribute(): string
    {
        $unit = $this->item->unit ?? 'pcs';
        return number_format($this->amount, 2) . ' ' . $unit;
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get formatted subtotal.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    /**
     * Get display information for the invoice item.
     */
    public function getDisplayInfoAttribute(): string
    {
        $itemName = $this->item->name ?? 'Item desconocido';
        $amount = $this->formatted_amount;
        $price = $this->formatted_price;
        $subtotal = $this->formatted_subtotal;
        
        return "{$itemName} - {$amount} x {$price} = {$subtotal}";
    }

    /**
     * Scope a query to filter by invoice.
     */
    public function scopeByInvoice(Builder $query, int $invoiceId): Builder
    {
        return $query->where('invoice_id', $invoiceId);
    }

    /**
     * Scope a query to filter by item.
     */
    public function scopeByItem(Builder $query, int $itemId): Builder
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Convert the model to an array for API responses.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'item_id' => $this->item_id,
            'amount' => $this->amount,
            'price' => $this->price,
            'subtotal' => $this->subtotal,
            'formatted_amount' => $this->formatted_amount,
            'formatted_price' => $this->formatted_price,
            'formatted_subtotal' => $this->formatted_subtotal,
            'display_info' => $this->display_info,
            'item' => $this->item?->toApiArray(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
