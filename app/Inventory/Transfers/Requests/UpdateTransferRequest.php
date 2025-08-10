<?php

namespace App\Inventory\Transfers\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransferRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'description' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'warehouse_id' => ['sometimes', 'nullable', 'integer', 'exists:warehouses,id'],
            'warehouse_source_id' => ['sometimes', 'required', 'integer', 'exists:warehouses,id', 'different:warehouse_destination_id'],
            'warehouse_destination_id' => ['sometimes', 'required', 'integer', 'exists:warehouses,id', 'different:warehouse_source_id'],
            'status' => ['sometimes', 'integer', 'in:0,1'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.item_id' => ['required_with:items', 'integer', 'exists:items,id'],
            'items.*.amount' => ['required_with:items', 'numeric', 'gt:0'],
        ];
    }
}

