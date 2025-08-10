<?php

namespace App\Inventory\Transfers\Models;

use App\Inventory\Item\Models\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferItem extends Model
{
    use HasFactory;

    protected $table = 'transfer_items';

    protected $fillable = [
        'transfer_id',
        'item_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function transfer(): BelongsTo { return $this->belongsTo(Transfer::class); }
    public function item(): BelongsTo { return $this->belongsTo(Item::class); }
}

