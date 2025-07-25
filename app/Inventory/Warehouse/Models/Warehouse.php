<?php

namespace App\Inventory\Warehouse\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\WarehouseFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Inventory\WarehouseItem\Models\WarehouseItem;
use App\Inventory\Item\Models\Item;

class Warehouse extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'warehouses';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'default',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status' => 'boolean',
        'default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return WarehouseFactory::new();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generar código automáticamente al crear
        static::creating(function ($warehouse) {
            if (empty($warehouse->code)) {
                $warehouse->code = static::generateCode();
            }
        });

        // Asegurar que solo un almacén tenga default = 1
        static::saving(function ($warehouse) {
            if ($warehouse->default) {
                // Si este almacén se está marcando como default,
                // desmarcar todos los demás
                static::where('id', '!=', $warehouse->id)
                      ->where('default', 1)
                      ->update(['default' => 0]);
            }
        });
    }

    /**
     * Generate a unique warehouse code.
     */
    public static function generateCode(): string
    {
        $lastWarehouse = static::orderBy('id', 'desc')->first();
        $nextNumber = $lastWarehouse ? $lastWarehouse->id + 1 : 1;
        
        return 'WH-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Get the status text attribute.
     */
    public function getStatusTextAttribute(): string
    {
        return $this->status ? 'Activo' : 'Inactivo';
    }

    /**
     * Get the default text attribute.
     */
    public function getDefaultTextAttribute(): string
    {
        return $this->default ? 'Sí' : 'No';
    }

    /**
     * Check if this warehouse is the default one.
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * Set this warehouse as default.
     */
    public function setAsDefault(): bool
    {
        return $this->update(['default' => true]);
    }

    /**
     * Remove default status from this warehouse.
     */
    public function removeDefault(): bool
    {
        return $this->update(['default' => false]);
    }

    /**
     * Scope a query to only include active warehouses.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include inactive warehouses.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', false);
    }

    /**
     * Scope a query to only include default warehouse.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('default', true);
    }

    /**
     * Scope a query to only include non-default warehouses.
     */
    public function scopeNonDefault(Builder $query): Builder
    {
        return $query->where('default', false);
    }

    /**
     * Scope a query to search warehouses by term.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus(Builder $query, bool $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by name.
     */
    public function scopeByName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    /**
     * Scope a query to filter by code.
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', 'like', "%{$code}%");
    }

    /**
     * Get the warehouse items for this warehouse.
     */
    public function warehouseItems(): HasMany
    {
        return $this->hasMany(WarehouseItem::class);
    }

    /**
     * Get the items available in this warehouse through the pivot table.
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'warehouse_items')
                    ->withPivot('quantity_available')
                    ->withTimestamps();
    }

    /**
     * Get items with stock in this warehouse.
     */
    public function itemsWithStock(): BelongsToMany
    {
        return $this->items()->wherePivot('quantity_available', '>', 0);
    }

    /**
     * Check if the warehouse code is unique.
     */
    public static function isCodeUnique(string $code, ?int $excludeId = null): bool
    {
        $query = static::where('code', $code);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Find warehouse by code.
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    /**
     * Get the default warehouse.
     */
    public static function getDefault(): ?self
    {
        return static::where('default', true)->first();
    }

    /**
     * Check if there is a default warehouse.
     */
    public static function hasDefault(): bool
    {
        return static::where('default', true)->exists();
    }

    /**
     * Get warehouses with filters.
     */
    public static function getFiltered(array $filters = []): Builder
    {
        $query = static::query();

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['name'])) {
            $query->byName($filters['name']);
        }

        if (!empty($filters['code'])) {
            $query->byCode($filters['code']);
        }

        return $query;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Determine if the warehouse is active.
     */
    public function isActive(): bool
    {
        return $this->status === true;
    }

    /**
     * Determine if the warehouse is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === false;
    }

    /**
     * Activate the warehouse.
     */
    public function activate(): bool
    {
        return $this->update(['status' => true]);
    }

    /**
     * Deactivate the warehouse.
     */
    public function deactivate(): bool
    {
        return $this->update(['status' => false]);
    }

    /**
     * Toggle the warehouse status.
     */
    public function toggleStatus(): bool
    {
        return $this->update(['status' => !$this->status]);
    }

    /**
     * Get a formatted display name for the warehouse.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }

    /**
     * Get a short description for the warehouse.
     */
    public function getShortDescriptionAttribute(): string
    {
        if (empty($this->description)) {
            return 'Sin descripción';
        }

        return strlen($this->description) > 100 
            ? substr($this->description, 0, 100) . '...' 
            : $this->description;
    }

    /**
     * Convert the model instance to an array for API responses.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'status_text' => $this->status_text,
            'default' => $this->default,
            'default_text' => $this->default_text,
            'display_name' => $this->display_name,
            'short_description' => $this->short_description,
            'is_active' => $this->isActive(),
            'is_inactive' => $this->isInactive(),
            'is_default' => $this->isDefault(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
