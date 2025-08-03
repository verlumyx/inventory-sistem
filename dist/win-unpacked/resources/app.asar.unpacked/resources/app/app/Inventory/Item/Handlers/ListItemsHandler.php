<?php

namespace App\Inventory\Item\Handlers;

use App\Inventory\Item\Contracts\ItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ListItemsHandler
{
    public function __construct(
        private ItemRepositoryInterface $itemRepository
    ) {}

    /**
     * Handle the list items request.
     */
    public function handle(array $filters = []): Collection
    {
        if (empty($filters)) {
            return $this->itemRepository->all();
        }

        return $this->itemRepository->getForExport($filters);
    }

    /**
     * Handle the list items request with pagination.
     */
    public function handlePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        if (empty($filters)) {
            return $this->itemRepository->paginate($perPage);
        }

        return $this->itemRepository->getFiltered($filters, $perPage);
    }

    /**
     * Handle search request.
     */
    public function handleSearch(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->itemRepository->search($term, $perPage);
    }

    /**
     * Handle get active items request.
     */
    public function handleActive(): Collection
    {
        return $this->itemRepository->getActive();
    }

    /**
     * Handle get inactive items request.
     */
    public function handleInactive(): Collection
    {
        return $this->itemRepository->getInactive();
    }

    /**
     * Handle get latest items request.
     */
    public function handleLatest(int $limit = 10): Collection
    {
        return $this->itemRepository->getLatest($limit);
    }

    /**
     * Handle get statistics request.
     */
    public function handleStatistics(): array
    {
        return $this->itemRepository->getStatistics();
    }
}
