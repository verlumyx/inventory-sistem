<?php

namespace Database\Factories;

use App\Inventory\Invoice\Models\Invoice;
use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Inventory\Invoice\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
        ];
    }

    /**
     * Create an invoice for a specific warehouse.
     */
    public function forWarehouse(int $warehouseId): static
    {
        return $this->state(fn (array $attributes) => [
            'warehouse_id' => $warehouseId,
        ]);
    }

    /**
     * Create an invoice with a specific code.
     */
    public function withCode(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
        ]);
    }
}
