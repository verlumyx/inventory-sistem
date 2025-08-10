<?php

namespace App\Inventory\Adjustments\Repositories;

use App\Inventory\Adjustments\Contracts\AdjustmentRepositoryInterface;
use App\Inventory\Adjustments\Models\Adjustment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdjustmentRepository implements AdjustmentRepositoryInterface
{
    public function getAll(array $filters = []): Collection
    {
        return Adjustment::getFiltered($filters ?? [])
            ->with(['warehouse', 'items.item'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Adjustment::getFiltered($filters ?? [])
            ->with(['warehouse', 'items.item'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Adjustment
    {
        return Adjustment::with(['warehouse', 'items.item'])->find($id);
    }

    public function findByCode(string $code): ?Adjustment
    {
        return Adjustment::with(['warehouse', 'items.item'])
            ->where('code', $code)
            ->first();
    }

    public function create(array $data): Adjustment
    {
        return Adjustment::create($data);
    }

    public function update(Adjustment $model, array $data): Adjustment
    {
        $model->update($data);
        return $model;
    }

    public function exists(int $id): bool
    {
        return Adjustment::where('id', $id)->exists();
    }

    public function isCodeUnique(string $code, ?int $excludeId = null): bool
    {
        $query = Adjustment::where('code', $code);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return !$query->exists();
    }

    public function getActive(): Collection
    {
        return Adjustment::byStatus(1)->orderBy('created_at', 'desc')->get();
    }

    public function getInactive(): Collection
    {
        return Adjustment::byStatus(0)->orderBy('created_at', 'desc')->get();
    }

    public function count(array $filters = []): int
    {
        return Adjustment::getFiltered($filters ?? [])->count();
    }


    /**
     * Crear ajuste con items en una transacción.
     */
    public function createWithItems(array $data, array $items): Adjustment
    {
        return \DB::transaction(function () use ($data, $items) {
            $adjustment = $this->create($data);

            foreach ($items as $row) {
                $adjustment->items()->create([
                    'item_id' => (int) $row['item_id'],
                    'amount' => (float) $row['amount'],
                ]);
            }

            return $adjustment->load(['warehouse', 'items.item']);
        });
    }



}
