<?php

namespace App\Inventory\ExchangeRate\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExchangeRateRequest extends FormRequest
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
            'rate' => [
                'required',
                'numeric',
                'min:0.0001',
                'max:999999.9999',
                'regex:/^\d+(\.\d{1,4})?$/', // Máximo 4 decimales
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rate.required' => 'La tasa de cambio es obligatoria.',
            'rate.numeric' => 'La tasa de cambio debe ser un número.',
            'rate.min' => 'La tasa de cambio debe ser mayor a 0.',
            'rate.max' => 'La tasa de cambio no puede ser mayor a 999,999.9999.',
            'rate.regex' => 'La tasa de cambio puede tener máximo 4 decimales.',
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
            'rate' => 'tasa de cambio',
        ];
    }

    /**
     * Get the validated rate as float.
     */
    public function getValidatedRate(): float
    {
        return (float) $this->validated()['rate'];
    }
}
