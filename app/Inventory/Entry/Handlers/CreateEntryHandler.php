<?php

namespace App\Inventory\Entry\Handlers;

use App\Inventory\Entry\Contracts\EntryRepositoryInterface;
use App\Inventory\Entry\Models\Entry;
use App\Inventory\Entry\Exceptions\EntryValidationException;
use Illuminate\Support\Facades\Log;

class CreateEntryHandler
{
    public function __construct(
        private EntryRepositoryInterface $entryRepository
    ) {}

    /**
     * Handle the create entry request.
     */
    public function handle(array $data): Entry
    {
        try {
            Log::info('Creando entrada', ['data' => $data]);

            // Establecer valores por defecto
            $data = array_merge([
                'status' => true,
            ], $data);

            // Crear la entrada (el código se genera automáticamente en el modelo)
            $entry = $this->entryRepository->create($data);

            Log::info('Entrada creada exitosamente', [
                'entry_id' => $entry->id,
                'entry_code' => $entry->code,
            ]);

            return $entry;

        } catch (\Exception $e) {
            Log::error('Error al crear entrada', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle the create entry with items request.
     */
    public function handleWithItems(array $entryData, array $items): Entry
    {
        try {
            Log::info('Creando entrada con items', [
                'entry_data' => $entryData,
                'items_count' => count($items),
            ]);

            // Validar items
            $this->validateItems($items);

            // Establecer valores por defecto para la entrada
            $entryData = array_merge([
                'status' => true,
            ], $entryData);

            // Crear la entrada con items
            $entry = $this->entryRepository->createWithItems($entryData, $items);

            Log::info('Entrada con items creada exitosamente', [
                'entry_id' => $entry->id,
                'entry_code' => $entry->code,
                'items_count' => $entry->entryItems->count(),
            ]);

            return $entry;

        } catch (\Exception $e) {
            Log::error('Error al crear entrada con items', [
                'entry_data' => $entryData,
                'items' => $items,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate the data before creating.
     */
    public function validate(array $data): array
    {
        $errors = [];

        // Validar nombre requerido
        if (empty($data['name'])) {
            $errors['name'] = 'El nombre es requerido.';
        }

        // Validar longitud del nombre
        if (!empty($data['name']) && strlen($data['name']) > 255) {
            $errors['name'] = 'El nombre no puede tener más de 255 caracteres.';
        }

        // Validar descripción si se proporciona
        if (!empty($data['description']) && strlen($data['description']) > 65535) {
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
     * Check if the data is valid.
     */
    public function isValid(array $data): bool
    {
        return empty($this->validate($data));
    }

    /**
     * Check if the items data is valid.
     */
    public function isItemsValid(array $items): bool
    {
        try {
            $this->validateItems($items);
            return true;
        } catch (EntryValidationException $e) {
            return false;
        }
    }
}
