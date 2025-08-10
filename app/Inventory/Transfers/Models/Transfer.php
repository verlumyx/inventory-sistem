<?php

namespace App\Inventory\Transfers\Models;

use App\Inventory\Item\Models\Item;
use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transfer extends Model
{
    use HasFactory;

    protected $table = 'transfers';

    protected $fillable = [
        'code',
        'description',
        'warehouse_id',
        'warehouse_source_id',
        'warehouse_destination_id',
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
        return 'TR-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
    }

    // Relaciones
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function sourceWarehouse(): BelongsTo { return $this->belongsTo(Warehouse::class, 'warehouse_source_id'); }
    public function destinationWarehouse(): BelongsTo { return $this->belongsTo(Warehouse::class, 'warehouse_destination_id'); }
    public function items(): HasMany { return $this->hasMany(TransferItem::class); }

    // Scopes básicos
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('code', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeByStatus(Builder $query, int $status): Builder
    { return $query->where('status', $status); }
}

