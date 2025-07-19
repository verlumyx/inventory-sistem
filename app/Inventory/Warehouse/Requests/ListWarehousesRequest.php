<?php

namespace App\Inventory\Warehouse\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListWarehousesRequest extends FormRequest
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
            'page' => [
                'sometimes',
                'integer',
                'min:1',
            ],
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
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
            'sort_by' => [
                'sometimes',
                'string',
                'in:name,code,status,created_at,updated_at',
            ],
            'sort_direction' => [
                'sometimes',
                'string',
                'in:asc,desc',
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
            'page.integer' => 'La página debe ser un número entero.',
            'page.min' => 'La página debe ser mayor a 0.',
            
            'per_page.integer' => 'Los elementos por página deben ser un número entero.',
            'per_page.min' => 'Los elementos por página deben ser mayor a 0.',
            'per_page.max' => 'Los elementos por página no pueden exceder 100.',
            
            'search.string' => 'El término de búsqueda debe ser una cadena de texto.',
            'search.max' => 'El término de búsqueda no puede exceder 255 caracteres.',
            
            'status.boolean' => 'El estado debe ser verdadero o falso.',
            
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no puede exceder 20 caracteres.',
            
            'sort_by.in' => 'El campo de ordenamiento no es válido.',
            'sort_direction.in' => 'La dirección de ordenamiento debe ser asc o desc.',
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
            'page' => 'página',
            'per_page' => 'elementos por página',
            'search' => 'búsqueda',
            'status' => 'estado',
            'name' => 'nombre',
            'code' => 'código',
            'sort_by' => 'ordenar por',
            'sort_direction' => 'dirección de ordenamiento',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Establecer valores por defecto
        $this->merge([
            'per_page' => $this->per_page ?? 15,
            'sort_by' => $this->sort_by ?? 'created_at',
            'sort_direction' => $this->sort_direction ?? 'desc',
        ]);
    }
}
