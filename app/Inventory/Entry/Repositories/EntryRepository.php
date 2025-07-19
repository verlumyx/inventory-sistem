<?php

namespace App\Inventory\Entry\Repositories;

use App\Inventory\Entry\Contracts\EntryRepositoryInterface;
use App\Inventory\Entry\Models\Entry;
use App\Inventory\Entry\Models\EntryItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EntryRepository implements EntryRepositoryInterface
{
    /**
     * Get all entries.
     */
    public function all(): Collection
    {
        return Entry::orderBy('created_at', 'desc')->get();
    }

    /**
     * Get entries with pagination.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Entry::orderBy('code', 'desc')->paginate($perPage);
    }

    /**
     * Get entries with filters and pagination.
     */
    public function getFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Entry::getFiltered($filters)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find an entry by ID.
     */
    public function find(int $id): ?Entry
    {
        return Entry::find($id);
    }

    /**
     * Find an entry by ID or fail.
     */
    public function findOrFail(int $id): Entry
    {
        return Entry::findOrFail($id);
    }

    /**
     * Find an entry by code.
     */
    public function findByCode(string $code): ?Entry
    {
        return Entry::where('code', $code)->first();
    }

    /**
     * Create a new entry.
     */
    public function create(array $data): Entry
    {
        return Entry::create($data);
    }

    /**
     * Update an existing entry.
     */
    public function update(int $id, array $data): Entry
    {
        $entry = $this->findOrFail($id);
        $entry->update($data);
        return $entry->fresh();
    }

    /**
     * Get active entries.
     */
    public function getActive(): Collection
    {
        return Entry::active()->orderBy('name')->get();
    }

    /**
     * Get inactive entries.
     */
    public function getInactive(): Collection
    {
        return Entry::inactive()->orderBy('name')->get();
    }

    /**
     * Search entries by term.
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return Entry::search($term)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Check if a code is unique.
     */
    public function isCodeUnique(string $code, ?int $excludeId = null): bool
    {
        return Entry::isCodeUnique($code, $excludeId);
    }

    /**
     * Activate an entry.
     */
    public function activate(int $id): bool
    {
        $entry = $this->findOrFail($id);
        return $entry->activate();
    }

    /**
     * Deactivate an entry.
     */
    public function deactivate(int $id): bool
    {
        $entry = $this->findOrFail($id);
        return $entry->deactivate();
    }

    /**
     * Toggle entry status.
     */
    public function toggleStatus(int $id): bool
    {
        $entry = $this->findOrFail($id);
        return $entry->toggleStatus();
    }

    /**
     * Get entries count.
     */
    public function count(): int
    {
        return Entry::count();
    }

    /**
     * Get active entries count.
     */
    public function countActive(): int
    {
        return Entry::active()->count();
    }

    /**
     * Get inactive entries count.
     */
    public function countInactive(): int
    {
        return Entry::inactive()->count();
    }

    /**
     * Get entries created today.
     */
    public function getCreatedToday(): Collection
    {
        return Entry::whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get entries created this week.
     */
    public function getCreatedThisWeek(): Collection
    {
        return Entry::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get entries created this month.
     */
    public function getCreatedThisMonth(): Collection
    {
        return Entry::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the latest entries.
     */
    public function getLatest(int $limit = 10): Collection
    {
        return Entry::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get entries by status with pagination.
     */
    public function getByStatus(bool $status, int $perPage = 15): LengthAwarePaginator
    {
        return Entry::byStatus($status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Bulk update entries status.
     */
    public function bulkUpdateStatus(array $ids, bool $status): int
    {
        return Entry::whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * Get entries for export.
     */
    public function getForExport(array $filters = []): Collection
    {
        return Entry::getFiltered($filters)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get entries statistics.
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

    /**
     * Create entry with items.
     */
    public function createWithItems(array $entryData, array $items): Entry
    {
        return DB::transaction(function () use ($entryData, $items) {
            // Crear la entrada
            $entry = $this->create($entryData);

            // Crear los items de la entrada
            foreach ($items as $itemData) {
                $entry->entryItems()->create([
                    'item_id' => $itemData['item_id'],
                    'warehouse_id' => $itemData['warehouse_id'],
                    'amount' => $itemData['amount'],
                ]);
            }

            return $entry->load('entryItems.item', 'entryItems.warehouse');
        });
    }

    /**
     * Update entry with items.
     */
    public function updateWithItems(int $id, array $entryData, array $items): Entry
    {
        return DB::transaction(function () use ($id, $entryData, $items) {
            // Actualizar la entrada
            $entry = $this->update($id, $entryData);

            // Eliminar items existentes
            $entry->entryItems()->delete();

            // Crear los nuevos items
            foreach ($items as $itemData) {
                $entry->entryItems()->create([
                    'item_id' => $itemData['item_id'],
                    'warehouse_id' => $itemData['warehouse_id'],
                    'amount' => $itemData['amount'],
                ]);
            }

            return $entry->load('entryItems.item', 'entryItems.warehouse');
        });
    }

    /**
     * Get entry with items.
     */
    public function getWithItems(int $id): Entry
    {
        return Entry::with('entryItems.item', 'entryItems.warehouse')
            ->findOrFail($id);
    }
}
