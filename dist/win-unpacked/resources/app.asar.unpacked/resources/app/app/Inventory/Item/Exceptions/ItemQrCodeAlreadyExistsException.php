<?php

namespace App\Inventory\Item\Exceptions;

use Exception;

class ItemQrCodeAlreadyExistsException extends Exception
{
    public function __construct(string $message = "El Código de barra del item ya existe", int $code = 409, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
