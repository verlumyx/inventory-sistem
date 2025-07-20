<?php

namespace App\Inventory\Item\Handlers;

use App\Inventory\Item\Contracts\ItemRepositoryInterface;
use App\Inventory\Item\Models\Item;
use App\Inventory\Item\Exceptions\ItemCodeAlreadyExistsException;
use App\Inventory\Item\Exceptions\ItemQrCodeAlreadyExistsException;

class CreateItemHandler
{
    public function __construct(
        private ItemRepositoryInterface $itemRepository
    ) {}

    /**
     * Handle the create item request.
     */
    public function handle(array $data): Item
    {
        // Validar que el Código de barra sea único si se proporciona
        if (!empty($data['qr_code']) && !$this->itemRepository->isQrCodeUnique($data['qr_code'])) {
            throw new ItemQrCodeAlreadyExistsException("El Código de barra '{$data['qr_code']}' ya existe.");
        }

        // Establecer valores por defecto
        $data = array_merge([
            'status' => true,
        ], $data);

        // Crear el item (el código se genera automáticamente en el modelo)
        return $this->itemRepository->create($data);
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



        // Validar Código de barra si se proporciona
        if (!empty($data['qr_code'])) {
            if (strlen($data['qr_code']) > 255) {
                $errors['qr_code'] = 'El Código de barra no puede tener más de 255 caracteres.';
            }

            if (!$this->itemRepository->isQrCodeUnique($data['qr_code'])) {
                $errors['qr_code'] = 'El Código de barra ya existe.';
            }
        }

        // Validar descripción si se proporciona
        if (!empty($data['description']) && strlen($data['description']) > 65535) {
            $errors['description'] = 'La descripción es demasiado larga.';
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
}
