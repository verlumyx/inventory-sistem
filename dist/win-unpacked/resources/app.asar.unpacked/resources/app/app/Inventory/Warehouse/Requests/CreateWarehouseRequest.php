<?php

namespace App\Inventory\Warehouse\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateWarehouseRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'status' => [
                'sometimes',
                'boolean',
            ],
            'code' => [
                'sometimes',
                'string',
                'max:20',
                'unique:warehouses,code',
                'regex:/^WH-\d{8}$/',
            ],
            'default' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del almacén es requerido.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede exceder los 255 caracteres.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no puede exceder los 1000 caracteres.',
            
            'status.boolean' => 'El estado debe ser verdadero o falso.',
            
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no puede exceder los 20 caracteres.',
            'code.unique' => 'Este código de almacén ya existe.',
            'code.regex' => 'El código debe tener el formato WH-00000000.',

            'default.boolean' => 'El campo por defecto debe ser verdadero o falso.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'status' => 'estado',
            'code' => 'código',
            'default' => 'por defecto',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Establecer estado por defecto si no se proporciona
        if (!$this->has('status')) {
            $this->merge(['status' => true]);
        }

        // Establecer default por defecto si no se proporciona
        if (!$this->has('default')) {
            $this->merge(['default' => false]);
        }

        // Limpiar espacios en blanco
        if ($this->has('name')) {
            $this->merge(['name' => trim($this->name)]);
        }

        if ($this->has('description')) {
            $this->merge(['description' => trim($this->description)]);
        }
    }
}
