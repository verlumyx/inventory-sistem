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
            'price' => $this->faker->boolean(80) ? $this->generatePrice($type) : null,
            'unit' => $this->faker->boolean(75) ? $this->generateUnit($type) : null,
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
     * Generate a realistic price based on item type.
     */
    private function generatePrice(string $type): float
    {
        $priceRanges = [
            'Laptop' => [800, 3500],
            'Monitor' => [150, 800],
            'Teclado' => [20, 150],
            'Mouse' => [10, 80],
            'Impresora' => [100, 500],
            'Escáner' => [80, 300],
            'Tablet' => [200, 1200],
            'Smartphone' => [300, 1500],
            'Cámara' => [150, 2000],
            'Proyector' => [300, 1500],
            'Altavoces' => [30, 200],
            'Auriculares' => [15, 300],
            'Disco Duro' => [50, 300],
            'Memoria USB' => [10, 50],
            'Cable' => [5, 30],
            'Adaptador' => [10, 50],
            'Silla' => [100, 800],
            'Mesa' => [150, 1000],
            'Escritorio' => [200, 1500],
            'Archivador' => [80, 400],
            'Lámpara' => [25, 150],
            'Calculadora' => [15, 100],
            'Teléfono' => [50, 300],
            'Router' => [40, 200],
            'Switch' => [30, 500],
            'Servidor' => [1000, 8000],
        ];

        $range = $priceRanges[$type] ?? [10, 100];
        return round($this->faker->randomFloat(2, $range[0], $range[1]), 2);
    }

    /**
     * Generate a realistic unit based on item type.
     */
    private function generateUnit(string $type): string
    {
        $unitMappings = [
            'Laptop' => ['pcs', 'unidad'],
            'Monitor' => ['pcs', 'unidad'],
            'Teclado' => ['pcs', 'unidad'],
            'Mouse' => ['pcs', 'unidad'],
            'Impresora' => ['pcs', 'unidad'],
            'Escáner' => ['pcs', 'unidad'],
            'Tablet' => ['pcs', 'unidad'],
            'Smartphone' => ['pcs', 'unidad'],
            'Cámara' => ['pcs', 'unidad'],
            'Proyector' => ['pcs', 'unidad'],
            'Altavoces' => ['pcs', 'par', 'unidad'],
            'Auriculares' => ['pcs', 'par', 'unidad'],
            'Disco Duro' => ['pcs', 'unidad'],
            'Memoria USB' => ['pcs', 'unidad'],
            'Cable' => ['pcs', 'metros', 'unidad'],
            'Adaptador' => ['pcs', 'unidad'],
            'Silla' => ['pcs', 'unidad'],
            'Mesa' => ['pcs', 'unidad'],
            'Escritorio' => ['pcs', 'unidad'],
            'Archivador' => ['pcs', 'unidad'],
            'Lámpara' => ['pcs', 'unidad'],
            'Calculadora' => ['pcs', 'unidad'],
            'Teléfono' => ['pcs', 'unidad'],
            'Router' => ['pcs', 'unidad'],
            'Switch' => ['pcs', 'unidad'],
            'Servidor' => ['pcs', 'unidad'],
        ];

        $units = $unitMappings[$type] ?? ['pcs', 'unidad', 'kg', 'litros', 'metros'];
        return $this->faker->randomElement($units);
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
