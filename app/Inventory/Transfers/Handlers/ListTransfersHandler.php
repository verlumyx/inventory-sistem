<?php

namespace App\Inventory\Transfers\Handlers;

use App\Inventory\Transfers\Contracts\TransferRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ListTransfersHandler
{
    public function __construct(private TransferRepositoryInterface $transferRepository) {}

    public function handleAll(array $filters = []): Collection
    {
        Log::info('Listando traslados', ['filters' => $filters]);
        return $this->transferRepository->getAll($filters);
    }

    public function handlePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        Log::info('Listando traslados paginados', ['filters' => $filters, 'per_page' => $perPage]);
        return $this->transferRepository->getPaginated($filters, $perPage);
    }
}

