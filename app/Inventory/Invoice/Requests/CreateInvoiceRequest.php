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
            'rate' => [
                'nullable',
                'numeric',
                'min:0.0001',
                'max:999999.9999',
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
            'rate.numeric' => 'La tasa de cambio debe ser un número.',
            'rate.min' => 'La tasa de cambio debe ser mayor a 0.',
            'rate.max' => 'La tasa de cambio no puede ser mayor a 999,999.9999.',
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
            'rate' => 'tasa de cambio',
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
        // Removed duplicate items validation to allow quantity accumulation
        // Items with the same ID will have their quantities summed in the frontend
    }

    /**
     * Get the validated data for invoice creation.
     */
    public function getInvoiceData(): array
    {
        $data = [
            'warehouse_id' => $this->warehouse_id,
        ];

        // Add rate if provided, otherwise use current exchange rate
        if ($this->has('rate') && $this->input('rate') !== null) {
            $data['rate'] = (float) $this->input('rate');
            \Log::info('Using provided rate', ['rate' => $data['rate']]);
        } else {
            // Get current exchange rate
            try {
                $data['rate'] = \App\Inventory\ExchangeRate\Models\ExchangeRate::getCurrentRate();
                \Log::info('Using current exchange rate', ['rate' => $data['rate']]);
            } catch (\Exception $e) {
                $data['rate'] = 1.0000; // Fallback to 1 if exchange rate not available
                \Log::info('Using fallback rate', ['rate' => $data['rate'], 'error' => $e->getMessage()]);
            }
        }

        \Log::info('Invoice data prepared', $data);
        return $data;
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
