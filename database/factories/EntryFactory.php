<?php

namespace Database\Factories;

use App\Inventory\Entry\Models\Entry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Inventory\Entry\Models\Entry>
 */
class EntryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Entry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $entryTypes = [
            'Compra de Equipos',
            'Recepción de Inventario',
            'Transferencia de Almacén',
            'Devolución de Cliente',
            'Ajuste de Inventario',
            'Entrada por Garantía',
            'Reposición de Stock',
            'Entrada de Emergencia',
            'Recepción de Donación',
            'Entrada por Reparación',
        ];

        $purposes = [
            'oficina',
            'producción',
            'ventas',
            'administración',
            'desarrollo',
            'soporte técnico',
            'recursos humanos',
            'contabilidad',
            'marketing',
            'logística',
        ];

        $type = $this->faker->randomElement($entryTypes);
        $purpose = $this->faker->randomElement($purposes);

        $name = $this->generateEntryName($type, $purpose);

        return [
            'name' => $name,
            'description' => $this->faker->boolean(70) ? $this->generateDescription($type, $purpose) : null,
            'status' => $this->faker->boolean(85), // 85% activas
        ];
    }

    /**
     * Generate a realistic entry name.
     */
    private function generateEntryName(string $type, string $purpose): string
    {
        $date = $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m');

        $names = [
            "{$type} - {$purpose} {$date}",
            "{$type} para {$purpose}",
            "Entrada {$type} - " . ucfirst($purpose),
            "{$type} departamento {$purpose}",
            "Recepción {$purpose} - {$date}",
        ];

        return $this->faker->randomElement($names);
    }

    /**
     * Generate a realistic description.
     */
    private function generateDescription(string $type, string $purpose): string
    {
        $descriptions = [
            "Entrada de {$type} destinada al área de {$purpose}. Incluye equipos y materiales necesarios para las operaciones diarias.",
            "Recepción de inventario para {$purpose}. Los items han sido verificados y están listos para su distribución.",
            "Transferencia de equipos desde almacén central hacia {$purpose}. Todos los items están en perfecto estado.",
            "Entrada de {$type} solicitada por el departamento de {$purpose} para cubrir necesidades operativas.",
            "Recepción de materiales y equipos para {$purpose}. Entrada procesada según procedimientos estándar.",
            "Entrada de emergencia para {$purpose}. Items críticos para mantener operaciones continuas.",
            "Reposición de stock para {$purpose}. Entrada programada según plan de inventario mensual.",
        ];

        return $this->faker->randomElement($descriptions);
    }

    /**
     * Indicate that the entry is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => true,
        ]);
    }

    /**
     * Indicate that the entry is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }

    /**
     * Create an entry with a specific type.
     */
    public function withType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $type . ' - ' . $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'description' => "Entrada de tipo {$type} procesada según procedimientos estándar.",
        ]);
    }

    /**
     * Create an entry for a specific purpose.
     */
    public function forPurpose(string $purpose): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => "Entrada para {$purpose} - " . $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m'),
            'description' => "Entrada destinada específicamente para el área de {$purpose}.",
        ]);
    }

    /**
     * Create an entry with recent date.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create an entry with old date.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 year', '-1 month'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', '-1 month'),
        ]);
    }
}
