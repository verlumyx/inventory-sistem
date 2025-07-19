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

            'qr_code.string' => 'El código QR debe ser una cadena de texto.',
            'qr_code.max' => 'El código QR no puede tener más de 255 caracteres.',
            'qr_code.unique' => 'El código QR ya existe. Por favor, elija otro.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción es demasiado larga.',
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

            'qr_code' => 'código QR',
            'description' => 'descripción',
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
