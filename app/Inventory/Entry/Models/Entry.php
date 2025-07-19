<?php

namespace App\Inventory\Entry\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Database\Factories\EntryFactory;

class Entry extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'entries';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
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
        return EntryFactory::new();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generar código automáticamente al crear
        static::creating(function ($entry) {
            if (empty($entry->code)) {
                $entry->code = static::generateCode();
            }
        });
    }

    /**
     * Generate a unique entry code.
     */
    public static function generateCode(): string
    {
        $lastEntry = static::orderBy('id', 'desc')->first();
        $nextNumber = $lastEntry ? $lastEntry->id + 1 : 1;
        
        return 'ET-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Get the status text attribute.
     */
    public function getStatusTextAttribute(): string
    {
        return $this->status ? 'Activo' : 'Inactivo';
    }

    /**
     * Scope a query to only include active entries.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include inactive entries.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', false);
    }

    /**
     * Scope a query to search entries by term.
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
     * Get entries with filters.
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
     * Determine if the entry is active.
     */
    public function isActive(): bool
    {
        return $this->status === true;
    }

    /**
     * Determine if the entry is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === false;
    }

    /**
     * Activate the entry.
     */
    public function activate(): bool
    {
        return $this->update(['status' => true]);
    }

    /**
     * Deactivate the entry.
     */
    public function deactivate(): bool
    {
        return $this->update(['status' => false]);
    }

    /**
     * Toggle the entry status.
     */
    public function toggleStatus(): bool
    {
        return $this->update(['status' => !$this->status]);
    }

    /**
     * Get a formatted display name for the entry.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }

    /**
     * Get a short description for the entry.
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
     * Get the entry items relationship.
     */
    public function entryItems(): HasMany
    {
        return $this->hasMany(EntryItem::class);
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
            'description' => $this->description,
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
