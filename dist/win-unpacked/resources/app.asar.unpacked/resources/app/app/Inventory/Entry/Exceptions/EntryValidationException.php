<?php

namespace App\Inventory\Entry\Exceptions;

use Exception;

class EntryValidationException extends Exception
{
    /**
     * The validation errors.
     */
    protected array $errors;

    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = 'Error de validación', array $errors = [], int $code = 422, ?Exception $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the validation errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Set the validation errors.
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Check if there are validation errors.
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get the first error message.
     */
    public function getFirstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }

        $firstKey = array_key_first($this->errors);
        $firstError = $this->errors[$firstKey];

        if (is_array($firstError)) {
            return is_array($firstError[0]) ? $firstError[0][0] : $firstError[0];
        }

        return $firstError;
    }
}
