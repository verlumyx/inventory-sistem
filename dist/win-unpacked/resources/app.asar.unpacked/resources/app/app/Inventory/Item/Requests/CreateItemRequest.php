<?php

namespace App\Inventory\Item\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateItemRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],

            'qr_code' => [
                'nullable',
                'string',
                'max:255',
                'unique:items,qr_code',
            ],
            'description' => [
                'nullable',
                'string',
                'max:65535',
            ],
            'price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],
            'unit' => [
                'nullable',
                'string',
                'max:50',
            ],
            'status' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del item es requerido.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',

            'qr_code.string' => 'El Código de barra debe ser una cadena de texto.',
            'qr_code.max' => 'El Código de barra no puede tener más de 255 caracteres.',
            'qr_code.unique' => 'El Código de barra ya existe. Por favor, elija otro.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción es demasiado larga.',
            'price.numeric' => 'El precio debe ser un número.',
            'price.min' => 'El precio no puede ser negativo.',
            'price.max' => 'El precio no puede ser mayor a 99,999,999.99.',
            'unit.string' => 'La unidad debe ser una cadena de texto.',
            'unit.max' => 'La unidad no puede tener más de 50 caracteres.',
            'status.boolean' => 'El estado debe ser verdadero o falso.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',

            'qr_code' => 'Código de barra',
            'description' => 'descripción',
            'price' => 'precio',
            'unit' => 'unidad',
            'status' => 'estado',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpiar y formatear datos
        $data = [];

        if ($this->has('name')) {
            $data['name'] = trim($this->name);
        }



        if ($this->has('qr_code')) {
            $data['qr_code'] = trim($this->qr_code) ?: null;
        }

        if ($this->has('description')) {
            $data['description'] = trim($this->description) ?: null;
        }

        if ($this->has('price')) {
            $data['price'] = $this->price ? (float) $this->price : null;
        }

        if ($this->has('unit')) {
            $unit = trim($this->unit);
            $data['unit'] = empty($unit) ? null : $unit;
        }

        if ($this->has('status')) {
            $data['status'] = filter_var($this->status, FILTER_VALIDATE_BOOLEAN);
        }

        $this->merge($data);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validaciones adicionales personalizadas
            
            // Validar que el nombre no contenga solo espacios
            if ($this->filled('name') && empty(trim($this->name))) {
                $validator->errors()->add('name', 'El nombre no puede estar vacío.');
            }


        });
    }
}
