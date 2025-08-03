<?php

namespace App\Inventory\Warehouse\Exceptions;

use Exception;

class WarehouseOperationException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message = 'Error en operación de almacén', int $code = 500)
    {
        parent::__construct($message, $code);
    }

    /**
     * Create exception for creation failure.
     *
     * @param string $reason
     * @return static
     */
    public static function creationFailed(string $reason = ''): static
    {
        $message = 'No se pudo crear el almacén';
        if ($reason) {
            $message .= ": {$reason}";
        }

        return new static($message, 500);
    }

    /**
     * Create exception for update failure.
     *
     * @param int $id
     * @param string $reason
     * @return static
     */
    public static function updateFailed(int $id, string $reason = ''): static
    {
        $message = "No se pudo actualizar el almacén con ID: {$id}";
        if ($reason) {
            $message .= ": {$reason}";
        }

        return new static($message, 500);
    }

    /**
     * Create exception for database error.
     *
     * @param string $operation
     * @return static
     */
    public static function databaseError(string $operation): static
    {
        return new static(
            "Error de base de datos durante la operación: {$operation}",
            500
        );
    }
}
