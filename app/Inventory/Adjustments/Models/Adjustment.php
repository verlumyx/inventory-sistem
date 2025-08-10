<?php

namespace App\Inventory\Adjustments\Models;

use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Adjustment extends Model
{
    use HasFactory;

    protected $table = 'adjustments';

    protected $fillable = [
        'code',
        'description',
        'warehouse_id',
        'type',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->code)) {
                $model->code = static::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $last = static::orderBy('id', 'desc')->first();
        $nextNumber = $last ? $last->id + 1 : 1;
        return 'AJ-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
    }

    // Relations
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AdjustmentItem::class);
    }

    // Scopes
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('code', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeByWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeByStatus(Builder $query, int $status): Builder
    {
        return $query->where('status', $status);
    }

    // Accessors
    public function getStatusTextAttribute(): string
    {
        return ((int) $this->status) === 1 ? 'Aplicado' : 'Pendiente';
    }

    public function getTypeTextAttribute(): string
    {
        return $this->type === 'negative' ? 'Negativo' : 'Positivo';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->code . ' - ' . ($this->description ? mb_strimwidth($this->description, 0, 40, '…') : 'Sin descripción');
    }

    public static function getFiltered(array $filters = []): Builder
    {
        $query = static::query();

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }
        if (!empty($filters['warehouse_id'])) {
            $query->byWarehouse($filters['warehouse_id']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->byStatus((int) $filters['status']);
        }

        return $query;
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'description' => $this->description,
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => $this->warehouse?->toApiArray(),
            'type' => $this->type,
            'type_text' => $this->type_text,
            'status' => (int) $this->status,
            'status_text' => $this->status_text,
            'is_pending' => $this->status === 0,
            'is_applied' => $this->status === 1,
            'can_edit' => $this->status === 0, // Solo se puede editar si está pendiente
            'display_name' => $this->display_name,
            'items_count' => $this->items()->count(),
            'items' => $this->items->map(fn($item) => $item->toApiArray()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

