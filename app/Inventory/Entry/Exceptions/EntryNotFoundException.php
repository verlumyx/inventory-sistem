<?php

namespace App\Inventory\Entry\Exceptions;

use Exception;

class EntryNotFoundException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = 'Entrada no encontrada', int $code = 404, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
