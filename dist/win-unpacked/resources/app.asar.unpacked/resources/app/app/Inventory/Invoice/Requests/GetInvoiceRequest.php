<?php

namespace App\Inventory\Invoice\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => [
                'sometimes',
                'integer',
                'min:1',
            ],
            'code' => [
                'sometimes',
                'string',
                'max:20',
                'regex:/^FV-\d{8}$/',
            ],
            'with_items' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'id.integer' => 'El ID debe ser un número entero.',
            'id.min' => 'El ID debe ser mayor a 0.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no puede tener más de 20 caracteres.',
            'code.regex' => 'El código debe tener el formato FV-00000001.',
            'with_items.boolean' => 'El parámetro with_items debe ser verdadero o falso.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'id' => 'ID',
            'code' => 'código',
            'with_items' => 'incluir items',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpiar espacios en blanco del código
        if ($this->has('code')) {
            $this->merge([
                'code' => trim(strtoupper($this->code)),
            ]);
        }

        // Convertir with_items a boolean
        if ($this->has('with_items')) {
            $this->merge([
                'with_items' => filter_var($this->with_items, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /**
     * Get the invoice ID from route parameter.
     */
    public function getInvoiceId(): ?int
    {
        return $this->route('invoice') ? (int) $this->route('invoice') : null;
    }

    /**
     * Get the invoice code from request.
     */
    public function getInvoiceCode(): ?string
    {
        return $this->code;
    }

    /**
     * Check if items should be included.
     */
    public function shouldIncludeItems(): bool
    {
        return $this->get('with_items', false);
    }

    /**
     * Check if searching by ID.
     */
    public function isSearchingById(): bool
    {
        return $this->getInvoiceId() !== null;
    }

    /**
     * Check if searching by code.
     */
    public function isSearchingByCode(): bool
    {
        return !empty($this->getInvoiceCode());
    }

    /**
     * Get search parameters.
     */
    public function getSearchParams(): array
    {
        $params = [];

        if ($this->isSearchingById()) {
            $params['id'] = $this->getInvoiceId();
        }

        if ($this->isSearchingByCode()) {
            $params['code'] = $this->getInvoiceCode();
        }

        $params['with_items'] = $this->shouldIncludeItems();

        return $params;
    }
}
