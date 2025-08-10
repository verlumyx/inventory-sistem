<?php

namespace App\Inventory\Adjustments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:65535'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'type' => ['required', 'string', 'in:positive,negative'],
            // Items dinámicos
            'items' => ['nullable', 'array'],
            'items.*.item_id' => ['required_with:items', 'integer', 'exists:items,id'],
            'items.*.amount' => ['required_with:items', 'numeric', 'min:0.01'],
        ];
    }

    public function getData(): array
    {
        return [
            'description' => trim((string) $this->input('description')) ?: null,
            'warehouse_id' => (int) $this->input('warehouse_id'),
            'type' => $this->input('type', 'positive'),
        ];
    }

    public function getItems(): array
    {
        return array_map(function ($row) {
            return [
                'item_id' => (int) $row['item_id'],
                'amount' => (float) $row['amount'],
            ];
        }, $this->input('items', []) ?? []);
    }
}

