<?php

namespace App\Inventory\Item\Exceptions;

use Exception;

class ItemNotFoundException extends Exception
{
    public function __construct(string $message = "Item no encontrado", int $code = 404, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
