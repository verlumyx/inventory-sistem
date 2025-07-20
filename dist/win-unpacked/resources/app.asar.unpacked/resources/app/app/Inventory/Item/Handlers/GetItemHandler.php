<?php

namespace App\Inventory\Item\Handlers;

use App\Inventory\Item\Contracts\ItemRepositoryInterface;
use App\Inventory\Item\Models\Item;
use App\Inventory\Item\Exceptions\ItemNotFoundException;

class GetItemHandler
{
    public function __construct(
        private ItemRepositoryInterface $itemRepository
    ) {}

    /**
     * Handle the get item by ID request.
     */
    public function handle(int $id): Item
    {
        $item = $this->itemRepository->find($id);
        
        if (!$item) {
            throw new ItemNotFoundException("Item con ID {$id} no encontrado.");
        }

        return $item;
    }

    /**
     * Handle the get item by code request.
     */
    public function handleByCode(string $code): Item
    {
        $item = $this->itemRepository->findByCode($code);
        
        if (!$item) {
            throw new ItemNotFoundException("Item con código '{$code}' no encontrado.");
        }

        return $item;
    }

    /**
     * Handle the get item by QR code request.
     */
    public function handleByQrCode(string $qrCode): Item
    {
        $item = $this->itemRepository->findByQrCode($qrCode);
        
        if (!$item) {
            throw new ItemNotFoundException("Item con Código de barra '{$qrCode}' no encontrado.");
        }

        return $item;
    }

    /**
     * Handle the get item or fail request.
     */
    public function handleOrFail(int $id): Item
    {
        return $this->itemRepository->findOrFail($id);
    }

    /**
     * Check if item exists by ID.
     */
    public function exists(int $id): bool
    {
        return $this->itemRepository->find($id) !== null;
    }

    /**
     * Check if item exists by code.
     */
    public function existsByCode(string $code): bool
    {
        return $this->itemRepository->findByCode($code) !== null;
    }

    /**
     * Check if item exists by QR code.
     */
    public function existsByQrCode(string $qrCode): bool
    {
        return $this->itemRepository->findByQrCode($qrCode) !== null;
    }

    /**
     * Get item with additional data for display.
     */
    public function handleForDisplay(int $id): array
    {
        $item = $this->handle($id);

        return [
            'item' => $item->toApiArray(),
            'metadata' => [
                'created_ago' => $item->created_at->diffForHumans(),
                'updated_ago' => $item->updated_at->diffForHumans(),
                'is_recently_created' => $item->created_at->isToday(),
                'is_recently_updated' => $item->updated_at->isToday(),
            ]
        ];
    }
}
