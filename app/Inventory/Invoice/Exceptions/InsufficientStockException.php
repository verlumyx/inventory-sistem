<?php

namespace App\Inventory\Invoice\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    protected array $stockErrors = [];

    public function __construct(array $stockErrors, string $message = 'Stock insuficiente para completar la operación')
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
        $message = "No se puede marcar la factura como pagada debido a stock insuficiente:\n\n";
        
        foreach ($this->stockErrors as $error) {
            $message .= "• {$error['item_name']}: Solicitado {$error['requested']}, Disponible {$error['available']}\n";
        }
        
        return $message;
    }
}
