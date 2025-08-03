<?php

namespace App\Inventory\Item\Handlers;

use App\Inventory\Item\Contracts\ItemRepositoryInterface;
use App\Inventory\Item\Models\Item;
use App\Inventory\Item\Exceptions\ItemNotFoundException;
use App\Inventory\Item\Exceptions\ItemCodeAlreadyExistsException;
use App\Inventory\Item\Exceptions\ItemQrCodeAlreadyExistsException;

class UpdateItemHandler
{
    public function __construct(
        private ItemRepositoryInterface $itemRepository
    ) {}

    /**
     * Handle the update item request.
     */
    public function handle(int $id, array $data): Item
    {
        // Verificar que el item existe
        $item = $this->itemRepository->find($id);
        if (!$item) {
            throw new ItemNotFoundException("Item con ID {$id} no encontrado.");
        }

        // Validar que el Código de barra sea único si se está cambiando
        if (isset($data['qr_code']) && $data['qr_code'] !== $item->qr_code) {
            if (!empty($data['qr_code']) && !$this->itemRepository->isQrCodeUnique($data['qr_code'], $id)) {
                throw new ItemQrCodeAlreadyExistsException("El Código de barra '{$data['qr_code']}' ya existe.");
            }
        }

        // Actualizar el item (el código no se puede cambiar)
        return $this->itemRepository->update($id, $data);
    }

    /**
     * Handle toggle status request.
     */
    public function handleToggleStatus(int $id): Item
    {
        $item = $this->itemRepository->find($id);
        if (!$item) {
            throw new ItemNotFoundException("Item con ID {$id} no encontrado.");
        }

        $this->itemRepository->toggleStatus($id);
        return $this->itemRepository->findOrFail($id);
    }

    /**
     * Handle activate request.
     */
    public function handleActivate(int $id): Item
    {
        $item = $this->itemRepository->find($id);
        if (!$item) {
            throw new ItemNotFoundException("Item con ID {$id} no encontrado.");
        }

        $this->itemRepository->activate($id);
        return $this->itemRepository->findOrFail($id);
    }

    /**
     * Handle deactivate request.
     */
    public function handleDeactivate(int $id): Item
    {
        $item = $this->itemRepository->find($id);
        if (!$item) {
            throw new ItemNotFoundException("Item con ID {$id} no encontrado.");
        }

        $this->itemRepository->deactivate($id);
        return $this->itemRepository->findOrFail($id);
    }

    /**
     * Validate the data before updating.
     */
    public function validate(int $id, array $data): array
    {
        $errors = [];
        $item = $this->itemRepository->find($id);

        if (!$item) {
            $errors['id'] = 'Item no encontrado.';
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

        // Validar precio si se proporciona
        if (isset($data['price'])) {
            if (empty($data['price']) || $data['price'] <= 0) {
                $errors['price'] = 'El precio es requerido y debe ser mayor a 0.';
            }
        }

        // Validar Código de barra si se está cambiando
        if (isset($data['qr_code']) && $data['qr_code'] !== $item->qr_code) {
            if (!empty($data['qr_code'])) {
                if (strlen($data['qr_code']) > 255) {
                    $errors['qr_code'] = 'El Código de barra no puede tener más de 255 caracteres.';
                }

                if (!$this->itemRepository->isQrCodeUnique($data['qr_code'], $id)) {
                    $errors['qr_code'] = 'El Código de barra ya existe.';
                }
            }
        }

        // Validar descripción si se proporciona
        if (isset($data['description']) && !empty($data['description']) && strlen($data['description']) > 65535) {
            $errors['description'] = 'La descripción es demasiado larga.';
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
