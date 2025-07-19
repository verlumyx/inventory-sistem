<?php

namespace App\Inventory\Item\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Database\Factories\ItemFactory;

class Item extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'qr_code',
        'description',
        'price',
        'unit',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'boolean',
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
        return ItemFactory::new();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generar código automáticamente al crear
        static::creating(function ($item) {
            if (empty($item->code)) {
                $item->code = static::generateCode();
            }
        });
    }

    /**
     * Generate a unique item code.
     */
    public static function generateCode(): string
    {
        $lastItem = static::orderBy('id', 'desc')->first();
        $nextNumber = $lastItem ? $lastItem->id + 1 : 1;
        
        return 'IT-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Get the status text attribute.
     */
    public function getStatusTextAttribute(): string
    {
        return $this->status ? 'Activo' : 'Inactivo';
    }

    /**
     * Scope a query to only include active items.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include inactive items.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', false);
    }

    /**
     * Scope a query to search items by term.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('qr_code', 'like', "%{$term}%")
                  ->orWhere('unit', 'like', "%{$term}%");
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
     * Scope a query to filter by QR code.
     */
    public function scopeByQrCode(Builder $query, string $qrCode): Builder
    {
        return $query->where('qr_code', 'like', "%{$qrCode}%");
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopeByPriceRange(Builder $query, ?float $minPrice = null, ?float $maxPrice = null): Builder
    {
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        return $query;
    }

    /**
     * Scope a query to filter by unit.
     */
    public function scopeByUnit(Builder $query, string $unit): Builder
    {
        return $query->where('unit', 'like', "%{$unit}%");
    }

    /**
     * Check if a code is unique.
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
     * Check if a QR code is unique.
     */
    public static function isQrCodeUnique(string $qrCode, ?int $excludeId = null): bool
    {
        $query = static::where('qr_code', $qrCode);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    /**
     * Get items with filters.
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

        if (!empty($filters['qr_code'])) {
            $query->byQrCode($filters['qr_code']);
        }

        if (!empty($filters['unit'])) {
            $query->byUnit($filters['unit']);
        }

        if (isset($filters['min_price']) || isset($filters['max_price'])) {
            $query->byPriceRange($filters['min_price'] ?? null, $filters['max_price'] ?? null);
        }

        return $query;
    }

    /**
     * Determine if the item is active.
     */
    public function isActive(): bool
    {
        return $this->status === true;
    }

    /**
     * Determine if the item is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === false;
    }

    /**
     * Activate the item.
     */
    public function activate(): bool
    {
        return $this->update(['status' => true]);
    }

    /**
     * Deactivate the item.
     */
    public function deactivate(): bool
    {
        return $this->update(['status' => false]);
    }

    /**
     * Toggle the item status.
     */
    public function toggleStatus(): bool
    {
        return $this->update(['status' => !$this->status]);
    }

    /**
     * Get a formatted display name for the item.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }

    /**
     * Get a short description for the item.
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
     * Convert the model to an array for API responses.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'qr_code' => $this->qr_code,
            'description' => $this->description,
            'price' => $this->price,
            'unit' => $this->unit,
            'status' => $this->status,
            'status_text' => $this->status_text,
            'display_name' => $this->display_name,
            'short_description' => $this->short_description,
            'is_active' => $this->isActive(),
            'is_inactive' => $this->isInactive(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
