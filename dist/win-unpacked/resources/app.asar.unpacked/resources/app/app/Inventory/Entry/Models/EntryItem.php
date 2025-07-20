<?php

namespace App\Inventory\Entry\Models;

use App\Inventory\Item\Models\Item;
use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntryItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'entry_items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'entry_id',
        'item_id',
        'warehouse_id',
        'amount',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [];

    /**
     * Get the entry that owns the entry item.
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    /**
     * Get the item that belongs to the entry item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the warehouse that belongs to the entry item.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Scope a query to filter by entry.
     */
    public function scopeByEntry(Builder $query, int $entryId): Builder
    {
        return $query->where('entry_id', $entryId);
    }

    /**
     * Scope a query to filter by item.
     */
    public function scopeByItem(Builder $query, int $itemId): Builder
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope a query to filter by warehouse.
     */
    public function scopeByWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope a query to filter by amount range.
     */
    public function scopeByAmountRange(Builder $query, ?float $minAmount = null, ?float $maxAmount = null): Builder
    {
        if ($minAmount !== null) {
            $query->where('amount', '>=', $minAmount);
        }
        
        if ($maxAmount !== null) {
            $query->where('amount', '<=', $maxAmount);
        }
        
        return $query;
    }

    /**
     * Get entry items with filters.
     */
    public static function getFiltered(array $filters = []): Builder
    {
        $query = static::query();

        if (!empty($filters['entry_id'])) {
            $query->byEntry($filters['entry_id']);
        }

        if (!empty($filters['item_id'])) {
            $query->byItem($filters['item_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->byWarehouse($filters['warehouse_id']);
        }

        if (isset($filters['min_amount']) || isset($filters['max_amount'])) {
            $query->byAmountRange($filters['min_amount'] ?? null, $filters['max_amount'] ?? null);
        }

        return $query;
    }

    /**
     * Get the total amount for an entry.
     */
    public static function getTotalAmountForEntry(int $entryId): float
    {
        return static::where('entry_id', $entryId)->sum('amount');
    }

    /**
     * Get the total amount for an item across all entries.
     */
    public static function getTotalAmountForItem(int $itemId): float
    {
        return static::where('item_id', $itemId)->sum('amount');
    }

    /**
     * Get the total amount for a warehouse across all entries.
     */
    public static function getTotalAmountForWarehouse(int $warehouseId): float
    {
        return static::where('warehouse_id', $warehouseId)->sum('amount');
    }

    /**
     * Check if an item exists in an entry.
     */
    public static function itemExistsInEntry(int $entryId, int $itemId): bool
    {
        return static::where('entry_id', $entryId)
                    ->where('item_id', $itemId)
                    ->exists();
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
     * Get display information for the entry item.
     */
    public function getDisplayInfoAttribute(): string
    {
        $itemName = $this->item->name ?? 'Item desconocido';
        $warehouseName = $this->warehouse->name ?? 'Almacén desconocido';
        $amount = $this->formatted_amount;
        
        return "{$itemName} - {$amount} en {$warehouseName}";
    }

    /**
     * Convert the model to an array for API responses.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'entry_id' => $this->entry_id,
            'item_id' => $this->item_id,
            'warehouse_id' => $this->warehouse_id,
            'amount' => $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'display_info' => $this->display_info,
            'item' => $this->item?->toApiArray(),
            'warehouse' => $this->warehouse?->toApiArray(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
