<?php

namespace App\Inventory\Item\Repositories;

use App\Inventory\Item\Contracts\ItemRepositoryInterface;
use App\Inventory\Item\Models\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class ItemRepository implements ItemRepositoryInterface
{
    /**
     * Get all items.
     */
    public function all(): Collection
    {
        return Item::orderBy('created_at', 'desc')->get();
    }

    /**
     * Get items with pagination.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Item::orderBy('code', 'desc')->paginate($perPage);
    }

    /**
     * Get items with filters and pagination.
     */
    public function getFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Item::getFiltered($filters)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find an item by ID.
     */
    public function find(int $id): ?Item
    {
        return Item::find($id);
    }

    /**
     * Find an item by ID or fail.
     */
    public function findOrFail(int $id): Item
    {
        return Item::findOrFail($id);
    }

    /**
     * Find an item by code.
     */
    public function findByCode(string $code): ?Item
    {
        return Item::where('code', $code)->first();
    }

    /**
     * Find an item by QR code.
     */
    public function findByQrCode(string $qrCode): ?Item
    {
        return Item::where('qr_code', $qrCode)->first();
    }

    /**
     * Create a new item.
     */
    public function create(array $data): Item
    {
        return Item::create($data);
    }

    /**
     * Update an existing item.
     */
    public function update(int $id, array $data): Item
    {
        $item = $this->findOrFail($id);
        $item->update($data);
        return $item->fresh();
    }



    /**
     * Get active items.
     */
    public function getActive(): Collection
    {
        return Item::active()->orderBy('name')->get();
    }

    /**
     * Get inactive items.
     */
    public function getInactive(): Collection
    {
        return Item::inactive()->orderBy('name')->get();
    }

    /**
     * Search items by term.
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return Item::search($term)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Check if a code is unique.
     */
    public function isCodeUnique(string $code, ?int $excludeId = null): bool
    {
        return Item::isCodeUnique($code, $excludeId);
    }

    /**
     * Check if a QR code is unique.
     */
    public function isQrCodeUnique(string $qrCode, ?int $excludeId = null): bool
    {
        return Item::isQrCodeUnique($qrCode, $excludeId);
    }

    /**
     * Activate an item.
     */
    public function activate(int $id): bool
    {
        $item = $this->findOrFail($id);
        return $item->activate();
    }

    /**
     * Deactivate an item.
     */
    public function deactivate(int $id): bool
    {
        $item = $this->findOrFail($id);
        return $item->deactivate();
    }

    /**
     * Toggle item status.
     */
    public function toggleStatus(int $id): bool
    {
        $item = $this->findOrFail($id);
        return $item->toggleStatus();
    }

    /**
     * Get items count.
     */
    public function count(): int
    {
        return Item::count();
    }

    /**
     * Get active items count.
     */
    public function countActive(): int
    {
        return Item::active()->count();
    }

    /**
     * Get inactive items count.
     */
    public function countInactive(): int
    {
        return Item::inactive()->count();
    }

    /**
     * Get items created today.
     */
    public function getCreatedToday(): Collection
    {
        return Item::whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get items created this week.
     */
    public function getCreatedThisWeek(): Collection
    {
        return Item::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get items created this month.
     */
    public function getCreatedThisMonth(): Collection
    {
        return Item::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the latest items.
     */
    public function getLatest(int $limit = 10): Collection
    {
        return Item::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get items by status with pagination.
     */
    public function getByStatus(bool $status, int $perPage = 15): LengthAwarePaginator
    {
        return Item::byStatus($status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Bulk update items status.
     */
    public function bulkUpdateStatus(array $ids, bool $status): int
    {
        return Item::whereIn('id', $ids)->update(['status' => $status]);
    }



    /**
     * Get items for export.
     */
    public function getForExport(array $filters = []): Collection
    {
        return Item::getFiltered($filters)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get items statistics.
     */
    public function getStatistics(): array
    {
        $total = $this->count();
        $active = $this->countActive();
        $inactive = $this->countInactive();
        $createdToday = $this->getCreatedToday()->count();
        $createdThisWeek = $this->getCreatedThisWeek()->count();
        $createdThisMonth = $this->getCreatedThisMonth()->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'active_percentage' => $total > 0 ? round(($active / $total) * 100, 2) : 0,
            'inactive_percentage' => $total > 0 ? round(($inactive / $total) * 100, 2) : 0,
            'created_today' => $createdToday,
            'created_this_week' => $createdThisWeek,
            'created_this_month' => $createdThisMonth,
        ];
    }
}
