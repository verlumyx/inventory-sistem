<?php

namespace App\Inventory\Entry\Handlers;

use App\Inventory\Entry\Contracts\EntryRepositoryInterface;
use App\Inventory\Entry\Models\Entry;
use App\Inventory\Entry\Exceptions\EntryNotFoundException;
use Illuminate\Support\Facades\Log;

class GetEntryHandler
{
    public function __construct(
        private EntryRepositoryInterface $entryRepository
    ) {}

    /**
     * Handle the get entry by ID request.
     */
    public function handle(int $id): Entry
    {
        try {
            Log::info('Obteniendo entrada por ID', ['entry_id' => $id]);

            $entry = $this->entryRepository->find($id);
            
            if (!$entry) {
                throw new EntryNotFoundException("Entrada con ID {$id} no encontrada.");
            }

            Log::info('Entrada obtenida exitosamente', [
                'entry_id' => $entry->id,
                'entry_code' => $entry->code,
            ]);

            return $entry;

        } catch (\Exception $e) {
            Log::error('Error al obtener entrada por ID', [
                'entry_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle the get entry by code request.
     */
    public function handleByCode(string $code): Entry
    {
        try {
            Log::info('Obteniendo entrada por código', ['entry_code' => $code]);

            $entry = $this->entryRepository->findByCode($code);
            
            if (!$entry) {
                throw new EntryNotFoundException("Entrada con código '{$code}' no encontrada.");
            }

            Log::info('Entrada obtenida exitosamente por código', [
                'entry_id' => $entry->id,
                'entry_code' => $entry->code,
            ]);

            return $entry;

        } catch (\Exception $e) {
            Log::error('Error al obtener entrada por código', [
                'entry_code' => $code,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle the get entry with items request.
     */
    public function handleWithItems(int $id): Entry
    {
        try {
            Log::info('Obteniendo entrada con items', ['entry_id' => $id]);

            $entry = $this->entryRepository->getWithItems($id);

            Log::info('Entrada con items obtenida exitosamente', [
                'entry_id' => $entry->id,
                'entry_code' => $entry->code,
                'items_count' => $entry->entryItems->count(),
            ]);

            return $entry;

        } catch (\Exception $e) {
            Log::error('Error al obtener entrada con items', [
                'entry_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle the get entry or fail request.
     */
    public function handleOrFail(int $id): Entry
    {
        try {
            Log::info('Obteniendo entrada o fallo', ['entry_id' => $id]);

            return $this->entryRepository->findOrFail($id);

        } catch (\Exception $e) {
            Log::error('Error al obtener entrada o fallo', [
                'entry_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if entry exists by ID.
     */
    public function exists(int $id): bool
    {
        try {
            Log::info('Verificando existencia de entrada', ['entry_id' => $id]);

            return $this->entryRepository->find($id) !== null;

        } catch (\Exception $e) {
            Log::error('Error al verificar existencia de entrada', [
                'entry_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if entry exists by code.
     */
    public function existsByCode(string $code): bool
    {
        try {
            Log::info('Verificando existencia de entrada por código', ['entry_code' => $code]);

            return $this->entryRepository->findByCode($code) !== null;

        } catch (\Exception $e) {
            Log::error('Error al verificar existencia de entrada por código', [
                'entry_code' => $code,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get entry with additional data for display.
     */
    public function handleForDisplay(int $id): array
    {
        try {
            Log::info('Obteniendo entrada para mostrar', ['entry_id' => $id]);

            $entry = $this->handleWithItems($id);

            $displayData = [
                'entry' => $entry->toApiArray(),
                'items' => $entry->entryItems->map(function ($entryItem) {
                    return $entryItem->toApiArray();
                }),
                'metadata' => [
                    'created_ago' => $entry->created_at->diffForHumans(),
                    'updated_ago' => $entry->updated_at->diffForHumans(),
                    'is_recently_created' => $entry->created_at->isToday(),
                    'is_recently_updated' => $entry->updated_at->isToday(),
                    'total_items' => $entry->entryItems->count(),
                    'total_amount' => $entry->entryItems->sum('amount'),
                ]
            ];

            Log::info('Entrada para mostrar obtenida exitosamente', [
                'entry_id' => $entry->id,
                'items_count' => count($displayData['items']),
            ]);

            return $displayData;

        } catch (\Exception $e) {
            Log::error('Error al obtener entrada para mostrar', [
                'entry_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
