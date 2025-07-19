<?php

namespace App\Inventory\Entry\Handlers;

use App\Inventory\Entry\Contracts\EntryRepositoryInterface;
use App\Inventory\Entry\Models\Entry;
use App\Inventory\Entry\Exceptions\EntryNotFoundException;
use App\Inventory\Entry\Exceptions\EntryValidationException;
use Illuminate\Support\Facades\Log;

class UpdateEntryHandler
{
    public function __construct(
        private EntryRepositoryInterface $entryRepository
    ) {}

    /**
     * Handle the update entry request.
     */
    public function handle(int $id, array $data): Entry
    {
        try {
            Log::info('Actualizando entrada', [
                'entry_id' => $id,
                'data' => $data,
            ]);

            // Verificar que la entrada existe
            $entry = $this->entryRepository->find($id);
            if (!$entry) {
                throw new EntryNotFoundException("Entrada con ID {$id} no encontrada.");
            }

            // Actualizar la entrada
            $updatedEntry = $this->entryRepository->update($id, $data);

            Log::info('Entrada actualizada exitosamente', [
                'entry_id' => $updatedEntry->id,
                'entry_code' => $updatedEntry->code,
            ]);

            return $updatedEntry;

        } catch (\Exception $e) {
            Log::error('Error al actualizar entrada', [
                'entry_id' => $id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle the update entry with items request.
     */
    public function handleWithItems(int $id, array $entryData, array $items): Entry
    {
        try {
            Log::info('Actualizando entrada con items', [
                'entry_id' => $id,
                'entry_data' => $entryData,
                'items_count' => count($items),
            ]);

            // Verificar que la entrada existe
            $entry = $this->entryRepository->find($id);
            if (!$entry) {
                throw new EntryNotFoundException("Entrada con ID {$id} no encontrada.");
            }

            // Validar items
            $this->validateItems($items);

            // Actualizar la entrada con items
            $updatedEntry = $this->entryRepository->updateWithItems($id, $entryData, $items);

            Log::info('Entrada con items actualizada exitosamente', [
                'entry_id' => $updatedEntry->id,
                'entry_code' => $updatedEntry->code,
                'items_count' => $updatedEntry->entryItems->count(),
            ]);

            return $updatedEntry;

        } catch (\Exception $e) {
            Log::error('Error al actualizar entrada con items', [
                'entry_id' => $id,
                'entry_data' => $entryData,
                'items' => $items,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle toggle status request.
     */
    public function handleToggleStatus(int $id): Entry
    {
        try {
            Log::info('Cambiando estado de entrada', ['entry_id' => $id]);

            $entry = $this->entryRepository->find($id);
            if (!$entry) {
                throw new EntryNotFoundException("Entrada con ID {$id} no encontrada.");
            }

            $this->entryRepository->toggleStatus($id);
            $updatedEntry = $this->entryRepository->findOrFail($id);

            Log::info('Estado de entrada cambiado exitosamente', [
                'entry_id' => $updatedEntry->id,
                'new_status' => $updatedEntry->status,
            ]);

            return $updatedEntry;

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de entrada', [
                'entry_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle activate request.
     */
    public function handleActivate(int $id): Entry
    {
        try {
            Log::info('Activando entrada', ['entry_id' => $id]);

            $entry = $this->entryRepository->find($id);
            if (!$entry) {
                throw new EntryNotFoundException("Entrada con ID {$id} no encontrada.");
            }

            $this->entryRepository->activate($id);
            $updatedEntry = $this->entryRepository->findOrFail($id);

            Log::info('Entrada activada exitosamente', [
                'entry_id' => $updatedEntry->id,
            ]);

            return $updatedEntry;

        } catch (\Exception $e) {
            Log::error('Error al activar entrada', [
                'entry_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle deactivate request.
     */
    public function handleDeactivate(int $id): Entry
    {
        try {
            Log::info('Desactivando entrada', ['entry_id' => $id]);

            $entry = $this->entryRepository->find($id);
            if (!$entry) {
                throw new EntryNotFoundException("Entrada con ID {$id} no encontrada.");
            }

            $this->entryRepository->deactivate($id);
            $updatedEntry = $this->entryRepository->findOrFail($id);

            Log::info('Entrada desactivada exitosamente', [
                'entry_id' => $updatedEntry->id,
            ]);

            return $updatedEntry;

        } catch (\Exception $e) {
            Log::error('Error al desactivar entrada', [
                'entry_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate the data before updating.
     */
    public function validate(int $id, array $data): array
    {
        $errors = [];
        $entry = $this->entryRepository->find($id);

        if (!$entry) {
            $errors['id'] = 'Entrada no encontrada.';
            return $errors;
        }

        // Validar nombre si se proporciona
        if (isset($data['name'])) {
            if (empty($data['name'])) {
                $errors['name'] = 'El nombre es requerido.';
            } elseif (strlen($data['name']) > 255) {
                $errors['name'] = 'El nombre no puede tener más de 255 caracteres.';
            }
        }

        // Validar descripción si se proporciona
        if (isset($data['description']) && !empty($data['description']) && strlen($data['description']) > 65535) {
            $errors['description'] = 'La descripción es demasiado larga.';
        }

        return $errors;
    }

    /**
     * Validate items data.
     */
    public function validateItems(array $items): array
    {
        $errors = [];

        if (empty($items)) {
            $errors['items'] = 'Debe agregar al menos un item a la entrada.';
            return $errors;
        }

        foreach ($items as $index => $item) {
            $itemErrors = [];

            // Validar item_id
            if (empty($item['item_id'])) {
                $itemErrors['item_id'] = 'El item es requerido.';
            }

            // Validar warehouse_id
            if (empty($item['warehouse_id'])) {
                $itemErrors['warehouse_id'] = 'El almacén es requerido.';
            }

            // Validar amount
            if (empty($item['amount']) || $item['amount'] <= 0) {
                $itemErrors['amount'] = 'La cantidad debe ser mayor a 0.';
            }

            if (!empty($itemErrors)) {
                $errors["items.{$index}"] = $itemErrors;
            }
        }

        // Validar items duplicados
        $itemIds = array_column($items, 'item_id');
        $duplicates = array_diff_assoc($itemIds, array_unique($itemIds));
        
        if (!empty($duplicates)) {
            $errors['items'] = 'No se pueden agregar items duplicados en la misma entrada.';
        }

        if (!empty($errors)) {
            throw new EntryValidationException('Errores de validación en los items', $errors);
        }

        return $errors;
    }

    /**
     * Check if the data is valid for update.
     */
    public function isValid(int $id, array $data): bool
    {
        return empty($this->validate($id, $data));
    }
}
