<?php

namespace Database\Factories;

use App\Inventory\Item\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Inventory\Item\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Electrónicos', 'Oficina', 'Mobiliario', 'Herramientas', 'Equipos',
            'Suministros', 'Tecnología', 'Accesorios', 'Componentes', 'Materiales'
        ];

        $brands = [
            'Samsung', 'Apple', 'HP', 'Dell', 'Lenovo', 'Canon', 'Epson',
            'Microsoft', 'Logitech', 'Sony', 'LG', 'Asus', 'Acer', 'Generic'
        ];

        $itemTypes = [
            'Laptop', 'Monitor', 'Teclado', 'Mouse', 'Impresora', 'Escáner',
            'Tablet', 'Smartphone', 'Cámara', 'Proyector', 'Altavoces',
            'Auriculares', 'Disco Duro', 'Memoria USB', 'Cable', 'Adaptador',
            'Silla', 'Mesa', 'Escritorio', 'Archivador', 'Lámpara',
            'Calculadora', 'Teléfono', 'Router', 'Switch', 'Servidor'
        ];

        $category = $this->faker->randomElement($categories);
        $brand = $this->faker->randomElement($brands);
        $type = $this->faker->randomElement($itemTypes);

        $name = $brand . ' ' . $type;
        if ($this->faker->boolean(30)) {
            $name .= ' ' . $this->faker->randomElement(['Pro', 'Plus', 'Max', 'Mini', 'Lite']);
        }

        return [
            'name' => $name,
            'description' => $this->faker->boolean(70) ? $this->generateDescription($type, $brand, $category) : null,
            'qr_code' => $this->faker->boolean(60) ? $this->generateQrCode() : null,
            'status' => $this->faker->boolean(85), // 85% activos
        ];
    }

    /**
     * Generate a realistic description for the item.
     */
    private function generateDescription(string $type, string $brand, string $category): string
    {
        $descriptions = [
            'Laptop' => "Laptop {$brand} con procesador de alta gama, ideal para trabajo profesional y tareas exigentes.",
            'Monitor' => "Monitor {$brand} de alta resolución, perfecto para diseño gráfico y productividad.",
            'Teclado' => "Teclado {$brand} ergonómico con teclas mecánicas para mayor comodidad.",
            'Mouse' => "Mouse {$brand} óptico de precisión con diseño ergonómico.",
            'Impresora' => "Impresora {$brand} multifuncional con capacidad de impresión, escaneo y copia.",
            'Tablet' => "Tablet {$brand} con pantalla táctil de alta resolución y gran autonomía.",
            'Smartphone' => "Smartphone {$brand} con cámara avanzada y conectividad 5G.",
            'Silla' => "Silla ergonómica {$brand} con soporte lumbar ajustable.",
            'Mesa' => "Mesa de trabajo {$brand} resistente y funcional.",
        ];

        $baseDescription = $descriptions[$type] ?? "Producto {$brand} de categoría {$category} de alta calidad.";

        $features = [
            'Con garantía extendida',
            'Diseño moderno y elegante',
            'Fácil instalación y uso',
            'Compatible con múltiples sistemas',
            'Bajo consumo energético',
            'Materiales de primera calidad',
            'Tecnología de vanguardia',
            'Ideal para uso profesional'
        ];

        if ($this->faker->boolean(50)) {
            $baseDescription .= ' ' . $this->faker->randomElement($features) . '.';
        }

        return $baseDescription;
    }

    /**
     * Generate a QR code string.
     */
    private function generateQrCode(): string
    {
        return 'QR-' . strtoupper($this->faker->bothify('??##??##'));
    }

    /**
     * Indicate that the item is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => true,
        ]);
    }

    /**
     * Indicate that the item is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }

    /**
     * Indicate that the item has a QR code.
     */
    public function withQrCode(): static
    {
        return $this->state(fn (array $attributes) => [
            'qr_code' => $this->generateQrCode(),
        ]);
    }

    /**
     * Indicate that the item has no QR code.
     */
    public function withoutQrCode(): static
    {
        return $this->state(fn (array $attributes) => [
            'qr_code' => null,
        ]);
    }

    /**
     * Indicate that the item has a description.
     */
    public function withDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $this->generateDescription('Producto', 'Marca', 'General'),
        ]);
    }

    /**
     * Indicate that the item has no description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }
}
