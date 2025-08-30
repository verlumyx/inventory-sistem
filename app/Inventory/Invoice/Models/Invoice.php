<?php

namespace App\Inventory\Invoice\Models;

use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Database\Factories\InvoiceFactory;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'invoices';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'warehouse_id',
        'status',
        'rate',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status' => 'integer',
        'rate' => 'decimal:4',
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
        return InvoiceFactory::new();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generar código automáticamente al crear
        static::creating(function ($invoice) {
            if (empty($invoice->code)) {
                $invoice->code = static::generateCode();
            }
        });
    }

    /**
     * Generate a unique invoice code.
     */
    public static function generateCode(): string
    {
        $lastInvoice = static::orderBy('id', 'desc')->first();
        $nextNumber = $lastInvoice ? $lastInvoice->id + 1 : 1;

        return 'FV-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Get the warehouse that owns the invoice.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the invoice items for the invoice.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the company information (singleton pattern).
     */
    public function getCompanyAttribute()
    {
        return \App\Models\Company::getCompany();
    }

    /**
     * Scope a query to search invoices.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('code', 'like', "%{$term}%")
              ->orWhereHas('warehouse', function ($wq) use ($term) {
                  $wq->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%");
              });
        });
    }

    /**
     * Scope a query to filter by warehouse.
     */
    public function scopeByWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope a query to filter by code.
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus(Builder $query, int $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to get pending invoices (por pagar).
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 0);
    }

    /**
     * Scope a query to get paid invoices (pagadas).
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    /**
     * Get filtered invoices.
     */
    public static function getFiltered(array $filters = []): Builder
    {
        $query = static::query();

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->byWarehouse($filters['warehouse_id']);
        }

        if (!empty($filters['code'])) {
            $query->byCode($filters['code']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->byStatus($filters['status']);
        }

        return $query;
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
     * Find invoice by code.
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    /**
     * Get the display name attribute.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->code . ' - ' . $this->warehouse->name;
    }

    /**
     * Get the total amount attribute.
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->invoiceItems()->sum(\DB::raw('amount * price'));
    }

    /**
     * Get the items count attribute.
     */
    public function getItemsCountAttribute(): int
    {
        return $this->invoiceItems()->count();
    }

    /**
     * Get the status text attribute.
     */
    public function getStatusTextAttribute(): string
    {
        return $this->status ? 'Pagada' : 'Por pagar';
    }

    /**
     * Get the formatted rate attribute.
     */
    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate ?? 1.0000, 4);
    }

    /**
     * Check if rate should be displayed (different from 1).
     */
    public function getShouldShowRateAttribute(): bool
    {
        return ($this->rate ?? 1.0000) != 1.0000;
    }

    /**
     * Get total amount in bolivars (converted using rate).
     */
    public function getTotalAmountBsAttribute(): float
    {
        return $this->total_amount * ($this->rate ?? 1.0000);
    }

    /**
     * Get the is pending attribute.
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === 0;
    }

    /**
     * Get the is paid attribute.
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->status === 1;
    }

    /**
     * Check if the invoice can be edited.
     */
    public function getCanEditAttribute(): bool
    {
        return $this->status === 0; // Solo se puede editar si está por pagar
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(): bool
    {
        $this->status = 1;
        return $this->save();
    }

    /**
     * Mark invoice as pending.
     */
    public function markAsPending(): bool
    {
        $this->status = 0;
        return $this->save();
    }

    /**
     * Convert the model to an array for API responses.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => $this->warehouse?->toApiArray(),
            'status' => $this->status,
            'status_text' => $this->status_text,
            'is_pending' => $this->is_pending,
            'is_paid' => $this->is_paid,
            'can_edit' => $this->can_edit,
            'total_amount' => $this->total_amount,
            'total_amount_bs' => $this->total_amount_bs,
            'rate' => $this->rate ?? 1.0000,
            'formatted_rate' => $this->formatted_rate,
            'should_show_rate' => $this->should_show_rate,
            'items_count' => $this->items_count,
            'display_name' => $this->display_name,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
