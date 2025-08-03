<?php

namespace App\Inventory\Entry\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntryRequest extends FormRequest
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
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:65535',
            ],
            'status' => [
                'sometimes',
                'boolean',
            ],
            'items' => [
                'sometimes',
                'required',
                'array',
                'min:1',
            ],
            'items.*.item_id' => [
                'required',
                'integer',
                'exists:items,id',
            ],
            'items.*.warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
            ],
            'items.*.amount' => [
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
            'name.required' => 'El nombre es requerido.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción es demasiado larga.',
            'status.boolean' => 'El estado debe ser verdadero o falso.',
            'items.required' => 'Debe agregar al menos un item a la entrada.',
            'items.array' => 'Los items deben ser un arreglo.',
            'items.min' => 'Debe agregar al menos un item a la entrada.',
            'items.*.item_id.required' => 'El item es requerido.',
            'items.*.item_id.integer' => 'El item debe ser un número entero.',
            'items.*.item_id.exists' => 'El item seleccionado no existe.',
            'items.*.warehouse_id.required' => 'El almacén es requerido.',
            'items.*.warehouse_id.integer' => 'El almacén debe ser un número entero.',
            'items.*.warehouse_id.exists' => 'El almacén seleccionado no existe.',
            'items.*.amount.required' => 'La cantidad es requerida.',
            'items.*.amount.numeric' => 'La cantidad debe ser un número.',
            'items.*.amount.min' => 'La cantidad debe ser mayor a 0.',
            'items.*.amount.max' => 'La cantidad no puede ser mayor a 999,999.99.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'status' => 'estado',
            'items' => 'items',
            'items.*.item_id' => 'item',
            'items.*.warehouse_id' => 'almacén',
            'items.*.amount' => 'cantidad',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpiar y formatear datos
        $data = [];

        if ($this->has('name')) {
            $data['name'] = trim($this->name);
        }

        if ($this->has('description')) {
            $description = trim($this->description);
            $data['description'] = empty($description) ? null : $description;
        }

        if ($this->has('status')) {
            $data['status'] = filter_var($this->status, FILTER_VALIDATE_BOOLEAN);
        }

        // Limpiar y validar items si se proporcionan
        if ($this->has('items') && is_array($this->items)) {
            $cleanItems = [];
            foreach ($this->items as $item) {
                if (is_array($item)) {
                    $cleanItem = [];
                    
                    if (isset($item['item_id'])) {
                        $cleanItem['item_id'] = (int) $item['item_id'];
                    }
                    
                    if (isset($item['warehouse_id'])) {
                        $cleanItem['warehouse_id'] = (int) $item['warehouse_id'];
                    }
                    
                    if (isset($item['amount'])) {
                        $cleanItem['amount'] = (float) $item['amount'];
                    }
                    
                    $cleanItems[] = $cleanItem;
                }
            }
            $data['items'] = $cleanItems;
        }

        $this->merge($data);
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Agregar validación personalizada para items duplicados
        $items = $this->get('items', []);
        if (is_array($items) && count($items) > 0) {
            $itemIds = array_column($items, 'item_id');
            $duplicates = array_diff_assoc($itemIds, array_unique($itemIds));
            
            if (!empty($duplicates)) {
                $validator->errors()->add('items', 'No se pueden agregar items duplicados en la misma entrada.');
            }
        }

        parent::failedValidation($validator);
    }

    /**
     * Get the validated data with proper formatting.
     */
    public function getValidatedData(): array
    {
        $validated = $this->validated();
        
        // Separar datos de entrada y items
        $entryData = [];
        
        if (isset($validated['name'])) {
            $entryData['name'] = $validated['name'];
        }
        
        if (isset($validated['description'])) {
            $entryData['description'] = $validated['description'];
        }
        
        if (isset($validated['status'])) {
            $entryData['status'] = $validated['status'];
        }
        
        $items = $validated['items'] ?? null;
        
        return [
            'entry' => $entryData,
            'items' => $items,
        ];
    }
}
