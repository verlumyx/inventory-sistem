<?php

namespace App\Inventory\Adjustments\Models;

use App\Inventory\Item\Models\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdjustmentItem extends Model
{
    use HasFactory;

    protected $table = 'adjustment_items';

    protected $fillable = [
        'adjustment_id',
        'item_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(Adjustment::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function getDisplayInfoAttribute(): string
    {
        $itemName = $this->item->name ?? 'Item desconocido';
        return $itemName . ' - ' . number_format($this->amount, 2);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'adjustment_id' => $this->adjustment_id,
            'item_id' => $this->item_id,
            'amount' => $this->amount,
            'display_info' => $this->display_info,
            'item' => $this->item?->toApiArray(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

