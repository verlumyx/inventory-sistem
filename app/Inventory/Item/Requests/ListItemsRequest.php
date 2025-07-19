<?php

namespace App\Inventory\Item\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListItemsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajustar según las políticas de autorización
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'status' => [
                'sometimes',
                'in:true,false,1,0',
            ],
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'code' => [
                'sometimes',
                'string',
                'max:20',
            ],
            'qr_code' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
            'page' => [
                'sometimes',
                'integer',
                'min:1',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'search.string' => 'El término de búsqueda debe ser una cadena de texto.',
            'search.max' => 'El término de búsqueda no puede tener más de 255 caracteres.',
            'status.in' => 'El estado debe ser verdadero o falso.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no puede tener más de 20 caracteres.',
            'qr_code.string' => 'El código QR debe ser una cadena de texto.',
            'qr_code.max' => 'El código QR no puede tener más de 255 caracteres.',
            'per_page.integer' => 'El número de elementos por página debe ser un número entero.',
            'per_page.min' => 'El número de elementos por página debe ser al menos 1.',
            'per_page.max' => 'El número de elementos por página no puede ser mayor a 100.',
            'page.integer' => 'El número de página debe ser un número entero.',
            'page.min' => 'El número de página debe ser al menos 1.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'search' => 'término de búsqueda',
            'status' => 'estado',
            'name' => 'nombre',
            'code' => 'código',
            'qr_code' => 'código QR',
            'per_page' => 'elementos por página',
            'page' => 'página',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpiar espacios en blanco
        if ($this->has('search')) {
            $this->merge([
                'search' => trim($this->search),
            ]);
        }

        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }

        if ($this->has('code')) {
            $this->merge([
                'code' => trim($this->code),
            ]);
        }

        if ($this->has('qr_code')) {
            $this->merge([
                'qr_code' => trim($this->qr_code),
            ]);
        }
    }
}
