<?php

namespace App\Inventory\Item\Exceptions;

use Exception;

class ItemCodeAlreadyExistsException extends Exception
{
    public function __construct(string $message = "El código del item ya existe", int $code = 409, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
