<?php

namespace App\Inventory\Adjustments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'warehouse_id' => ['sometimes', 'integer', 'exists:warehouses,id'],
            'type' => ['sometimes', 'string', 'in:positive,negative'],
            'items' => ['nullable', 'array'],
            'items.*.item_id' => ['required_with:items', 'integer', 'exists:items,id'],
            'items.*.amount' => ['required_with:items', 'numeric', 'min:0.01'],
        ];
    }

    public function getData(): array
    {
        return array_filter([
            'description' => $this->has('description') ? (trim((string) $this->input('description')) ?: null) : null,
            'warehouse_id' => $this->has('warehouse_id') ? (int) $this->input('warehouse_id') : null,
            'type' => $this->has('type') ? $this->input('type') : null,
        ], fn($v) => !is_null($v));
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

