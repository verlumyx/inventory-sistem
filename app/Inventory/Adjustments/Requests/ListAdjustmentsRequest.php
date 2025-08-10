<?php

namespace App\Inventory\Adjustments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListAdjustmentsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'in:all,0,1'],
            'warehouse_id' => ['sometimes', 'integer', 'exists:warehouses,id'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
        ];
    }

    public function getFilters(): array
    {
        return array_filter([
            'search' => $this->input('search'),
            'status' => $this->input('status'),
            'warehouse_id' => $this->input('warehouse_id'),
        ], fn($v) => !is_null($v) && $v !== '');
    }

    public function getPaginationParams(): array
    {
        return [
            'page' => (int) $this->input('page', 1),
            'per_page' => (int) $this->input('per_page', 10),
        ];
    }
}

