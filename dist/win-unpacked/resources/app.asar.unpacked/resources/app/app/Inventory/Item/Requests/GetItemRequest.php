<?php

namespace App\Inventory\Item\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetItemRequest extends FormRequest
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
            'include' => [
                'sometimes',
                'string',
                'in:metadata,statistics,related',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'include.string' => 'El parámetro include debe ser una cadena de texto.',
            'include.in' => 'El parámetro include debe ser uno de: metadata, statistics, related.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'include' => 'incluir',
        ];
    }
}
