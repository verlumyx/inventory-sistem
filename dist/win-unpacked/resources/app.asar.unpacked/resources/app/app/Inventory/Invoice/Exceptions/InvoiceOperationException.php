<?php

namespace App\Inventory\Invoice\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class InvoiceOperationException extends Exception
{
    /**
     * Create a new operation exception instance.
     */
    public function __construct(string $message, int $code = 500, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        
        // Log automático de errores de operación
        Log::error('Invoice Operation Exception', [
            'message' => $message,
            'code' => $code,
            'previous' => $previous?->getMessage(),
            'trace' => $this->getTraceAsString(),
        ]);
    }

    /**
     * Create a new exception for creation failure.
     */
    public static function creationFailed(string $reason = null): self
    {
        $message = 'Error al crear la factura.';
        if ($reason) {
            $message .= " Razón: {$reason}";
        }
        
        return new self($message, 500);
    }

    /**
     * Create a new exception for update failure.
     */
    public static function updateFailed(int $invoiceId, string $reason = null): self
    {
        $message = "Error al actualizar la factura con ID {$invoiceId}.";
        if ($reason) {
            $message .= " Razón: {$reason}";
        }
        
        return new self($message, 500);
    }

    /**
     * Create a new exception for deletion failure.
     */
    public static function deletionFailed(int $invoiceId, string $reason = null): self
    {
        $message = "Error al eliminar la factura con ID {$invoiceId}.";
        if ($reason) {
            $message .= " Razón: {$reason}";
        }
        
        return new self($message, 500);
    }

    /**
     * Create a new exception for database operation failure.
     */
    public static function databaseError(string $operation, string $details = null): self
    {
        $message = "Error de base de datos durante la operación: {$operation}.";
        if ($details) {
            $message .= " Detalles: {$details}";
        }
        
        return new self($message, 500);
    }

    /**
     * Create a new exception for business rule violation.
     */
    public static function businessRuleViolation(string $rule): self
    {
        return new self("Violación de regla de negocio: {$rule}", 422);
    }

    /**
     * Create a new exception for concurrent modification.
     */
    public static function concurrentModification(int $invoiceId): self
    {
        return new self(
            "La factura con ID {$invoiceId} fue modificada por otro usuario. Por favor, recarga la página e intenta nuevamente.",
            409
        );
    }

    /**
     * Get the HTTP status code for this exception.
     */
    public function getStatusCode(): int
    {
        return $this->getCode() ?: 500;
    }

    /**
     * Get the error type for API responses.
     */
    public function getErrorType(): string
    {
        return 'invoice_operation_error';
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
