<?php

namespace App\Inventory\Invoice\Exceptions;

use Exception;

class InvoiceValidationException extends Exception
{
    protected array $errors;

    /**
     * Create a new validation exception instance.
     */
    public function __construct(string $message, array $errors = [], int $code = 422, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * Create a new exception for invalid warehouse.
     */
    public static function invalidWarehouse(int $warehouseId): self
    {
        return new self(
            'El almacén seleccionado no es válido.',
            ['warehouse_id' => "El almacén con ID {$warehouseId} no existe o no está activo."],
            422
        );
    }

    /**
     * Create a new exception for invalid items.
     */
    public static function invalidItems(array $errors): self
    {
        return new self(
            'Los items de la factura contienen errores.',
            ['items' => $errors],
            422
        );
    }

    /**
     * Create a new exception for duplicate items.
     */
    public static function duplicateItems(): self
    {
        return new self(
            'No se pueden agregar items duplicados en la misma factura.',
            ['items' => 'Items duplicados detectados.'],
            422
        );
    }

    /**
     * Create a new exception for empty items.
     */
    public static function emptyItems(): self
    {
        return new self(
            'La factura debe tener al menos un item.',
            ['items' => 'La factura no puede estar vacía.'],
            422
        );
    }

    /**
     * Create a new exception for invalid amounts.
     */
    public static function invalidAmounts(): self
    {
        return new self(
            'Las cantidades de los items deben ser mayores a cero.',
            ['items' => 'Cantidades inválidas detectadas.'],
            422
        );
    }

    /**
     * Create a new exception for invalid prices.
     */
    public static function invalidPrices(): self
    {
        return new self(
            'Los precios de los items deben ser mayores a cero.',
            ['items' => 'Precios inválidos detectados.'],
            422
        );
    }

    /**
     * Get the validation errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the HTTP status code for this exception.
     */
    public function getStatusCode(): int
    {
        return $this->getCode() ?: 422;
    }

    /**
     * Get the error type for API responses.
     */
    public function getErrorType(): string
    {
        return 'invoice_validation_error';
    }

    /**
     * Convert the exception to an array for API responses.
     */
    public function toArray(): array
    {
        return [
            'error' => $this->getErrorType(),
            'message' => $this->getMessage(),
            'errors' => $this->getErrors(),
            'status_code' => $this->getStatusCode(),
        ];
    }
}
