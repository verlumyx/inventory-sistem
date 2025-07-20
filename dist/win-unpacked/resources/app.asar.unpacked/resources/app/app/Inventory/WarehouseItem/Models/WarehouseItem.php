<?php

namespace App\Inventory\WarehouseItem\Models;

use App\Inventory\Warehouse\Models\Warehouse;
use App\Inventory\Item\Models\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'warehouse_items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'warehouse_id',
        'item_id',
        'quantity_available',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'warehouse_id' => 'integer',
        'item_id' => 'integer',
        'quantity_available' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [];

    /**
     * Get the warehouse that owns the warehouse item.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the item that owns the warehouse item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Scope a query to only include items with available quantity.
     */
    public function scopeWithStock(Builder $query): Builder
    {
        return $query->where('quantity_available', '>', 0);
    }

    /**
     * Scope a query to only include items without stock.
     */
    public function scopeWithoutStock(Builder $query): Builder
    {
        return $query->where('quantity_available', '<=', 0);
    }

    /**
     * Scope a query to filter by warehouse.
     */
    public function scopeByWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope a query to filter by item.
     */
    public function scopeByItem(Builder $query, int $itemId): Builder
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope a query to filter by minimum quantity.
     */
    public function scopeWithMinimumQuantity(Builder $query, float $minQuantity): Builder
    {
        return $query->where('quantity_available', '>=', $minQuantity);
    }

    /**
     * Check if there's enough stock for a given quantity.
     */
    public function hasEnoughStock(float $quantity): bool
    {
        return $this->quantity_available >= $quantity;
    }

    /**
     * Add stock to the warehouse item.
     */
    public function addStock(float $quantity): bool
    {
        return $this->increment('quantity_available', $quantity);
    }

    /**
     * Remove stock from the warehouse item.
     */
    public function removeStock(float $quantity): bool
    {
        if (!$this->hasEnoughStock($quantity)) {
            return false;
        }

        return $this->decrement('quantity_available', $quantity);
    }

    /**
     * Set the exact stock quantity.
     */
    public function setStock(float $quantity): bool
    {
        return $this->update(['quantity_available' => $quantity]);
    }

    /**
     * Get the display name for the warehouse item.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->item->display_name} en {$this->warehouse->display_name}";
    }

    /**
     * Get the stock status text.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->quantity_available > 0) {
            return 'En Stock';
        }
        
        return 'Sin Stock';
    }

    /**
     * Check if the item is in stock.
     */
    public function isInStock(): bool
    {
        return $this->quantity_available > 0;
    }

    /**
     * Check if the item is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity_available <= 0;
    }

    /**
     * Get or create a warehouse item record.
     */
    public static function findOrCreate(int $warehouseId, int $itemId, float $initialQuantity = 0): self
    {
        return static::firstOrCreate(
            [
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
            ],
            [
                'quantity_available' => $initialQuantity,
            ]
        );
    }

    /**
     * Get the total stock for an item across all warehouses.
     */
    public static function getTotalStockForItem(int $itemId): float
    {
        return static::where('item_id', $itemId)->sum('quantity_available');
    }

    /**
     * Get the total stock for a warehouse across all items.
     */
    public static function getTotalStockForWarehouse(int $warehouseId): float
    {
        return static::where('warehouse_id', $warehouseId)->sum('quantity_available');
    }

    /**
     * Convert the model to an array for API responses.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'warehouse_id' => $this->warehouse_id,
            'item_id' => $this->item_id,
            'quantity_available' => $this->quantity_available,
            'stock_status' => $this->stock_status,
            'is_in_stock' => $this->isInStock(),
            'is_out_of_stock' => $this->isOutOfStock(),
            'display_name' => $this->display_name,
            'warehouse' => $this->warehouse ? [
                'id' => $this->warehouse->id,
                'code' => $this->warehouse->code,
                'name' => $this->warehouse->name,
                'display_name' => $this->warehouse->display_name,
            ] : null,
            'item' => $this->item ? [
                'id' => $this->item->id,
                'code' => $this->item->code,
                'name' => $this->item->name,
                'display_name' => $this->item->display_name,
                'unit' => $this->item->unit ?? 'unidad',
            ] : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
