<?php

namespace App\Inventory\Adjustments\Handlers;

use App\Inventory\Adjustments\Contracts\AdjustmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ListAdjustmentsHandler
{
    public function __construct(
        private AdjustmentRepositoryInterface $repository
    ) {}

    public function handleAll(array $filters = []): Collection
    {
        try {
            $result = $this->repository->getAll($filters);
            Log::info('Lista de ajustes obtenida', ['filters' => $filters, 'count' => $result->count()]);
            return $result;
        } catch (\Exception $e) {
            Log::error('Error al listar ajustes', ['filters' => $filters, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function handlePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            $result = $this->repository->getPaginated($filters, $perPage);
            Log::info('Ajustes paginados obtenidos', ['filters' => $filters, 'per_page' => $perPage]);
            return $result;
        } catch (\Exception $e) {
            Log::error('Error al paginar ajustes', ['filters' => $filters, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}

