<?php

namespace App\Inventory\Transfers\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListTransfersRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'search' => ['sometimes','string','max:255'],
            'status' => ['sometimes','integer','in:0,1'],
            'per_page' => ['sometimes','integer','min:1','max:100'],
            'page' => ['sometimes','integer','min:1'],
            'sort_by' => ['sometimes','string','in:id,code,created_at,updated_at'],
            'sort_direction' => ['sometimes','string','in:asc,desc'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('search')) $this->merge(['search' => trim($this->search)]);
        $this->merge([
            'per_page' => $this->get('per_page', 15),
            'page' => $this->get('page', 1),
            'sort_by' => $this->get('sort_by', 'created_at'),
            'sort_direction' => $this->get('sort_direction', 'desc'),
        ]);
    }

    public function getFilters(): array
    {
        $filters = [];
        if ($this->filled('search')) $filters['search'] = $this->search;
        if ($this->filled('status')) $filters['status'] = (int)$this->status;
        return $filters;
    }

    public function getPaginationParams(): array
    { return ['per_page' => $this->get('per_page', 15), 'page' => $this->get('page', 1)]; }
}

