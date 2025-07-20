<?php

namespace App\Inventory\Invoice\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListInvoicesRequest extends FormRequest
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
            'search' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'warehouse_id' => [
                'sometimes',
                'integer',
                'exists:warehouses,id',
            ],
            'code' => [
                'sometimes',
                'string',
                'max:20',
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
            'sort_by' => [
                'sometimes',
                'string',
                'in:id,code,warehouse_id,created_at,updated_at',
            ],
            'sort_direction' => [
                'sometimes',
                'string',
                'in:asc,desc',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'search.string' => 'El término de búsqueda debe ser una cadena de texto.',
            'search.max' => 'El término de búsqueda no puede tener más de 255 caracteres.',
            'warehouse_id.integer' => 'El ID del almacén debe ser un número entero.',
            'warehouse_id.exists' => 'El almacén seleccionado no existe.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no puede tener más de 20 caracteres.',
            'per_page.integer' => 'El número de elementos por página debe ser un número entero.',
            'per_page.min' => 'El número de elementos por página debe ser al menos 1.',
            'per_page.max' => 'El número de elementos por página no puede ser mayor a 100.',
            'page.integer' => 'El número de página debe ser un número entero.',
            'page.min' => 'El número de página debe ser al menos 1.',
            'sort_by.in' => 'El campo de ordenamiento no es válido.',
            'sort_direction.in' => 'La dirección de ordenamiento debe ser asc o desc.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'search' => 'término de búsqueda',
            'warehouse_id' => 'almacén',
            'code' => 'código',
            'per_page' => 'elementos por página',
            'page' => 'página',
            'sort_by' => 'campo de ordenamiento',
            'sort_direction' => 'dirección de ordenamiento',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpiar espacios en blanco del término de búsqueda
        if ($this->has('search')) {
            $this->merge([
                'search' => trim($this->search),
            ]);
        }

        // Limpiar espacios en blanco del código
        if ($this->has('code')) {
            $this->merge([
                'code' => trim($this->code),
            ]);
        }

        // Establecer valores por defecto
        $this->merge([
            'per_page' => $this->get('per_page', 15),
            'page' => $this->get('page', 1),
            'sort_by' => $this->get('sort_by', 'created_at'),
            'sort_direction' => $this->get('sort_direction', 'desc'),
        ]);
    }

    /**
     * Get the filters for the query.
     */
    public function getFilters(): array
    {
        $filters = [];

        if ($this->filled('search')) {
            $filters['search'] = $this->search;
        }

        if ($this->filled('warehouse_id')) {
            $filters['warehouse_id'] = $this->warehouse_id;
        }

        if ($this->filled('code')) {
            $filters['code'] = $this->code;
        }

        return $filters;
    }

    /**
     * Get the pagination parameters.
     */
    public function getPaginationParams(): array
    {
        return [
            'per_page' => $this->get('per_page', 15),
            'page' => $this->get('page', 1),
        ];
    }

    /**
     * Get the sorting parameters.
     */
    public function getSortParams(): array
    {
        return [
            'sort_by' => $this->get('sort_by', 'created_at'),
            'sort_direction' => $this->get('sort_direction', 'desc'),
        ];
    }

    /**
     * Check if any filters are applied.
     */
    public function hasFilters(): bool
    {
        return $this->filled(['search', 'warehouse_id', 'code']);
    }

    /**
     * Check if search is being performed.
     */
    public function isSearching(): bool
    {
        return $this->filled('search');
    }
}
