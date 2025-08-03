<?php

namespace App\Inventory\Invoice\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
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
            'warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.item_id' => [
                'required',
                'integer',
                'exists:items,id',
            ],
            'items.*.amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],
            'items.*.price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'El almacén es requerido.',
            'warehouse_id.integer' => 'El ID del almacén debe ser un número entero.',
            'warehouse_id.exists' => 'El almacén seleccionado no existe.',
            'items.required' => 'La factura debe tener al menos un item.',
            'items.array' => 'Los items deben ser un arreglo.',
            'items.min' => 'La factura debe tener al menos un item.',
            'items.*.item_id.required' => 'El item es requerido.',
            'items.*.item_id.integer' => 'El ID del item debe ser un número entero.',
            'items.*.item_id.exists' => 'El item seleccionado no existe.',
            'items.*.amount.required' => 'La cantidad es requerida.',
            'items.*.amount.numeric' => 'La cantidad debe ser un número.',
            'items.*.amount.min' => 'La cantidad debe ser mayor a 0.',
            'items.*.amount.max' => 'La cantidad no puede ser mayor a 999,999.99.',
            'items.*.price.required' => 'El precio es requerido.',
            'items.*.price.numeric' => 'El precio debe ser un número.',
            'items.*.price.min' => 'El precio debe ser mayor a 0.',
            'items.*.price.max' => 'El precio no puede ser mayor a 999,999.99.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'warehouse_id' => 'almacén',
            'items' => 'items',
            'items.*.item_id' => 'item',
            'items.*.amount' => 'cantidad',
            'items.*.price' => 'precio',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpiar y formatear items si existen
        if ($this->has('items') && is_array($this->items)) {
            $cleanItems = [];
            
            foreach ($this->items as $item) {
                if (is_array($item)) {
                    $cleanItem = [];
                    
                    if (isset($item['item_id'])) {
                        $cleanItem['item_id'] = (int) $item['item_id'];
                    }
                    
                    if (isset($item['amount'])) {
                        $cleanItem['amount'] = (float) $item['amount'];
                    }
                    
                    if (isset($item['price'])) {
                        $cleanItem['price'] = (float) $item['price'];
                    }
                    
                    $cleanItems[] = $cleanItem;
                }
            }
            
            $this->merge(['items' => $cleanItems]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar items duplicados
            if ($this->has('items') && is_array($this->items)) {
                $itemIds = array_column($this->items, 'item_id');
                $duplicates = array_diff_assoc($itemIds, array_unique($itemIds));
                
                if (!empty($duplicates)) {
                    $validator->errors()->add('items', 'No se pueden agregar items duplicados en la misma factura.');
                }
            }
        });
    }

    /**
     * Get the validated data for invoice creation.
     */
    public function getInvoiceData(): array
    {
        return [
            'warehouse_id' => $this->warehouse_id,
        ];
    }

    /**
     * Get the validated items data.
     */
    public function getItemsData(): array
    {
        return $this->items ?? [];
    }

    /**
     * Check if the request has items.
     */
    public function hasItems(): bool
    {
        return !empty($this->items);
    }

    /**
     * Get the total amount of the invoice.
     */
    public function getTotalAmount(): float
    {
        $total = 0;
        
        if ($this->hasItems()) {
            foreach ($this->items as $item) {
                $total += ($item['amount'] ?? 0) * ($item['price'] ?? 0);
            }
        }
        
        return $total;
    }

    /**
     * Get the items count.
     */
    public function getItemsCount(): int
    {
        return count($this->items ?? []);
    }
}
