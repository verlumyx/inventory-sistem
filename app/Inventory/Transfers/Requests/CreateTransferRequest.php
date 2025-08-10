<?php

namespace App\Inventory\Transfers\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransferRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:65535'],
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'warehouse_source_id' => ['required', 'integer', 'exists:warehouses,id', 'different:warehouse_destination_id'],
            'warehouse_destination_id' => ['required', 'integer', 'exists:warehouses,id', 'different:warehouse_source_id'],
            'status' => ['sometimes', 'integer', 'in:0,1'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', 'exists:items,id'],
            'items.*.amount' => ['required', 'numeric', 'gt:0'],
        ];
    }
}

