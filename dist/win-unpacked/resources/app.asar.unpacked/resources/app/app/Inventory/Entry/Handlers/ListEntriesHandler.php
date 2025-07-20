<?php

namespace App\Inventory\Entry\Handlers;

use App\Inventory\Entry\Contracts\EntryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ListEntriesHandler
{
    public function __construct(
        private EntryRepositoryInterface $entryRepository
    ) {}

    /**
     * Handle the list entries request.
     */
    public function handle(array $filters = []): Collection
    {
        try {
            Log::info('Listando entradas', ['filters' => $filters]);

            if (empty($filters)) {
                return $this->entryRepository->all();
            }

            return $this->entryRepository->getForExport($filters);

        } catch (\Exception $e) {
            Log::error('Error al listar entradas', [
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle the list entries request with pagination.
     */
    public function handlePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            Log::info('Listando entradas paginadas', [
                'filters' => $filters,
                'per_page' => $perPage,
            ]);

            if (empty($filters)) {
                return $this->entryRepository->paginate($perPage);
            }

            return $this->entryRepository->getFiltered($filters, $perPage);

        } catch (\Exception $e) {
            Log::error('Error al listar entradas paginadas', [
                'filters' => $filters,
                'per_page' => $perPage,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle search request.
     */
    public function handleSearch(string $term, int $perPage = 15): LengthAwarePaginator
    {
        try {
            Log::info('Buscando entradas', [
                'term' => $term,
                'per_page' => $perPage,
            ]);

            return $this->entryRepository->search($term, $perPage);

        } catch (\Exception $e) {
            Log::error('Error al buscar entradas', [
                'term' => $term,
                'per_page' => $perPage,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle get active entries request.
     */
    public function handleActive(): Collection
    {
        try {
            Log::info('Obteniendo entradas activas');

            return $this->entryRepository->getActive();

        } catch (\Exception $e) {
            Log::error('Error al obtener entradas activas', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle get inactive entries request.
     */
    public function handleInactive(): Collection
    {
        try {
            Log::info('Obteniendo entradas inactivas');

            return $this->entryRepository->getInactive();

        } catch (\Exception $e) {
            Log::error('Error al obtener entradas inactivas', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle get latest entries request.
     */
    public function handleLatest(int $limit = 10): Collection
    {
        try {
            Log::info('Obteniendo últimas entradas', ['limit' => $limit]);

            return $this->entryRepository->getLatest($limit);

        } catch (\Exception $e) {
            Log::error('Error al obtener últimas entradas', [
                'limit' => $limit,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle get statistics request.
     */
    public function handleStatistics(): array
    {
        try {
            Log::info('Obteniendo estadísticas de entradas');

            return $this->entryRepository->getStatistics();

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de entradas', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
