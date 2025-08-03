<?php

namespace Database\Factories;

use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Inventory\Warehouse\Models\Warehouse>
 */
class WarehouseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Warehouse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' - Almacén',
            'description' => $this->faker->optional(0.7)->paragraph(),
            'status' => $this->faker->boolean(85), // 85% probabilidad de estar activo
        ];
    }

    /**
     * Indicate that the warehouse is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => true,
        ]);
    }

    /**
     * Indicate that the warehouse is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }

    /**
     * Indicate that the warehouse has no description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }

    /**
     * Create a warehouse with a specific name.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    /**
     * Create a warehouse with a specific code.
     */
    public function withCode(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
        ]);
    }

    /**
     * Create a main warehouse.
     */
    public function main(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Almacén Principal',
            'description' => 'Almacén principal de la empresa para productos generales',
            'status' => true,
        ]);
    }

    /**
     * Create a secondary warehouse.
     */
    public function secondary(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Almacén Secundario',
            'description' => 'Almacén secundario para productos específicos',
            'status' => true,
        ]);
    }

    /**
     * Create a warehouse for testing purposes.
     */
    public function testing(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Almacén de Pruebas',
            'description' => 'Almacén utilizado únicamente para pruebas del sistema',
            'status' => false,
        ]);
    }
}
