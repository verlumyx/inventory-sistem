<?php

namespace App\Inventory\Transfers\Contracts;

use App\Inventory\Transfers\Models\Transfer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TransferRepositoryInterface
{
    public function getAll(array $filters = []): Collection;
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?Transfer;
    public function findByCode(string $code): ?Transfer;
    public function create(array $data): Transfer;
    public function update(Transfer $transfer, array $data): Transfer;
    public function exists(int $id): bool;
    public function isCodeUnique(string $code, ?int $excludeId = null): bool;
    public function count(array $filters = []): int;
}

