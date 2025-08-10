<?php

namespace App\Inventory\Transfers\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    protected array $stockErrors = [];

    public function __construct(array $stockErrors, string $message = 'Stock insuficiente para completar el traslado')
    {
        $this->stockErrors = $stockErrors;
        parent::__construct($message);
    }

    public function getStockErrors(): array
    {
        return $this->stockErrors;
    }

    public function getFormattedMessage(): string
    {
        $message = "No se puede completar el traslado por stock insuficiente en el almacén origen:\n\n";

        foreach ($this->stockErrors as $error) {
            $message .= "• {$error['item_name']}: Solicitado {$error['requested']}, Disponible {$error['available']}\n";
        }

        return $message;
    }
}

