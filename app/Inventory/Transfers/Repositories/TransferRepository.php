<?php

namespace App\Inventory\Transfers\Repositories;

use App\Inventory\Transfers\Contracts\TransferRepositoryInterface;
use App\Inventory\Transfers\Models\Transfer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TransferRepository implements TransferRepositoryInterface
{
    public function query(array $filters = []): Builder
    {
        $query = Transfer::query()->with(['sourceWarehouse', 'destinationWarehouse']);

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }
        if (isset($filters['status'])) {
            $query->byStatus((int) $filters['status']);
        }

        return $query->orderByDesc('id');
    }

    public function getAll(array $filters = []): Collection
    { return $this->query($filters)->get(); }

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    { return $this->query($filters)->paginate($perPage); }

    public function findById(int $id): ?Transfer
    { return Transfer::with(['items.item', 'sourceWarehouse', 'destinationWarehouse'])->find($id); }

    public function findByCode(string $code): ?Transfer
    { return Transfer::where('code', $code)->first(); }

    public function create(array $data): Transfer
    { return Transfer::create($data); }

    public function update(Transfer $transfer, array $data): Transfer
    { $transfer->update($data); return $transfer; }

    public function exists(int $id): bool
    { return Transfer::where('id', $id)->exists(); }

    public function isCodeUnique(string $code, ?int $excludeId = null): bool
    {
        $q = Transfer::where('code', $code);
        if ($excludeId) { $q->where('id', '!=', $excludeId); }
        return !$q->exists();
    }

    public function count(array $filters = []): int
    { return $this->query($filters)->count(); }
}

