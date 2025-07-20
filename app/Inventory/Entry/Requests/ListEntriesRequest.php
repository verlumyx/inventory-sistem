<?php

namespace App\Inventory\Entry\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListEntriesRequest extends FormRequest
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
                'in:id,code,name,status,created_at,updated_at',
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
            'status.in' => 'El estado debe ser verdadero o falso.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no puede tener más de 20 caracteres.',
            'per_page.integer' => 'El número de elementos por página debe ser un número entero.',
            'per_page.min' => 'El número de elementos por página debe ser al menos 1.',
            'per_page.max' => 'El número de elementos por página no puede ser mayor a 100.',
            'page.integer' => 'El número de página debe ser un número entero.',
            'page.min' => 'El número de página debe ser al menos 1.',
            'sort_by.string' => 'El campo de ordenamiento debe ser una cadena de texto.',
            'sort_by.in' => 'El campo de ordenamiento debe ser uno de: id, code, name, status, created_at, updated_at.',
            'sort_direction.string' => 'La dirección de ordenamiento debe ser una cadena de texto.',
            'sort_direction.in' => 'La dirección de ordenamiento debe ser asc o desc.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'search' => 'búsqueda',
            'status' => 'estado',
            'name' => 'nombre',
            'code' => 'código',
            'per_page' => 'elementos por página',
            'page' => 'página',
            'sort_by' => 'ordenar por',
            'sort_direction' => 'dirección de ordenamiento',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpiar y formatear datos
        $data = [];

        if ($this->has('search')) {
            $data['search'] = trim($this->search);
        }

        if ($this->has('name')) {
            $data['name'] = trim($this->name);
        }

        if ($this->has('code')) {
            $data['code'] = trim($this->code);
        }

        if ($this->has('per_page')) {
            $data['per_page'] = (int) $this->per_page;
        }

        if ($this->has('page')) {
            $data['page'] = (int) $this->page;
        }

        if ($this->has('sort_by')) {
            $data['sort_by'] = trim($this->sort_by);
        }

        if ($this->has('sort_direction')) {
            $data['sort_direction'] = strtolower(trim($this->sort_direction));
        }

        $this->merge($data);
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

        if ($this->has('status')) {
            $filters['status'] = filter_var($this->status, FILTER_VALIDATE_BOOLEAN);
        }

        if ($this->filled('name')) {
            $filters['name'] = $this->name;
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
        return $this->filled(['search', 'status', 'name', 'code']);
    }

    /**
     * Check if search is being performed.
     */
    public function isSearching(): bool
    {
        return $this->filled('search');
    }
}
