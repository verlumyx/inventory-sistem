<?php

namespace App\Inventory\Warehouse\Exceptions;

use Exception;

class WarehouseValidationException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @param array $errors
     */
    public function __construct(string $message = 'Error de validación en almacén', protected array $errors = [])
    {
        parent::__construct($message, 422);
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Create exception for duplicate code.
     *
     * @param string $code
     * @return static
     */
    public static function duplicateCode(string $code): static
    {
        return new static(
            "El código de almacén '{$code}' ya existe",
            ['code' => ["El código '{$code}' ya está en uso"]]
        );
    }

    /**
     * Create exception for invalid status.
     *
     * @return static
     */
    public static function invalidStatus(): static
    {
        return new static(
            'Estado de almacén inválido',
            ['status' => ['El estado debe ser verdadero o falso']]
        );
    }

    /**
     * Create exception for required fields.
     *
     * @param array $fields
     * @return static
     */
    public static function requiredFields(array $fields): static
    {
        $errors = [];
        foreach ($fields as $field) {
            $errors[$field] = ["El campo {$field} es requerido"];
        }

        return new static(
            'Campos requeridos faltantes',
            $errors
        );
    }
}
