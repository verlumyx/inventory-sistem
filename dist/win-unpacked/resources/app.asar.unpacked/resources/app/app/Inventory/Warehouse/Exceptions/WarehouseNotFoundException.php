<?php

namespace App\Inventory\Warehouse\Exceptions;

use Exception;

class WarehouseNotFoundException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param int|string $identifier
     * @param string $type
     */
    public function __construct($identifier, string $type = 'ID')
    {
        $message = "Almacén no encontrado con {$type}: {$identifier}";
        parent::__construct($message, 404);
    }

    /**
     * Create exception for warehouse not found by ID.
     *
     * @param int $id
     * @return static
     */
    public static function byId(int $id): static
    {
        return new static($id, 'ID');
    }

    /**
     * Create exception for warehouse not found by code.
     *
     * @param string $code
     * @return static
     */
    public static function byCode(string $code): static
    {
        return new static($code, 'código');
    }
}
