<?php

namespace App\Inventory\Adjustments\Contracts;

use App\Inventory\Adjustments\Models\Adjustment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AdjustmentRepositoryInterface
{
    public function getAll(array $filters = []): Collection;
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?Adjustment;
    public function findByCode(string $code): ?Adjustment;
    public function create(array $data): Adjustment;
    public function update(Adjustment $model, array $data): Adjustment;
    public function exists(int $id): bool;
    public function isCodeUnique(string $code, ?int $excludeId = null): bool;
    public function getActive(): Collection;
    public function getInactive(): Collection;
    public function count(array $filters = []): int;

    // Nuevos métodos para items
    public function createWithItems(array $data, array $items): Adjustment;
}

