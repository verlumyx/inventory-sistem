<?php

namespace App\Inventory\Invoice\Exceptions;

use Exception;

class InvoiceNotFoundException extends Exception
{
    /**
     * Create a new exception instance for invoice not found by ID.
     */
    public static function byId(int $id): self
    {
        return new self("Factura con ID {$id} no encontrada.", 404);
    }

    /**
     * Create a new exception instance for invoice not found by code.
     */
    public static function byCode(string $code): self
    {
        return new self("Factura con código '{$code}' no encontrada.", 404);
    }

    /**
     * Create a new exception instance with custom message.
     */
    public static function withMessage(string $message): self
    {
        return new self($message, 404);
    }

    /**
     * Get the HTTP status code for this exception.
     */
    public function getStatusCode(): int
    {
        return $this->getCode() ?: 404;
    }

    /**
     * Get the error type for API responses.
     */
    public function getErrorType(): string
    {
        return 'invoice_not_found';
    }

    /**
     * Convert the exception to an array for API responses.
     */
    public function toArray(): array
    {
        return [
            'error' => $this->getErrorType(),
            'message' => $this->getMessage(),
            'status_code' => $this->getStatusCode(),
        ];
    }
}
