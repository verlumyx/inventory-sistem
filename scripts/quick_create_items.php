<?php

/**
 * Script rápido para crear items de muestra
 * Este script crea items directamente sin usar el factory completo
 */

// Datos de muestra para crear items
$sampleItems = [
    [
        'name' => 'Samsung Laptop Galaxy Pro',
        'description' => 'Laptop Samsung con procesador Intel i7, 16GB RAM, SSD 512GB. Ideal para trabajo profesional.',
        'price' => 1299.99,
        'unit' => 'pcs',
        'qr_code' => 'QR-SAM001LP',
        'status' => 1
    ],
    [
        'name' => 'HP Monitor 24 Pulgadas',
        'description' => 'Monitor HP Full HD de 24 pulgadas con tecnología IPS y conectividad HDMI.',
        'price' => 189.99,
        'unit' => 'unidad',
        'qr_code' => 'QR-HP024MON',
        'status' => 1
    ],
    [
        'name' => 'Apple MacBook Air M2',
        'description' => 'MacBook Air con chip M2, 8GB RAM, SSD 256GB. Ultraligero y potente.',
        'price' => 1199.00,
        'unit' => 'pcs',
        'qr_code' => 'QR-APL002MBA',
        'status' => 1
    ],
    [
        'name' => 'Logitech Teclado Mecánico',
        'description' => 'Teclado mecánico Logitech con switches Cherry MX y retroiluminación RGB.',
        'price' => 89.99,
        'unit' => 'pcs',
        'qr_code' => 'QR-LOG003TEC',
        'status' => 1
    ],
    [
        'name' => 'Dell Mouse Inalámbrico',
        'description' => 'Mouse inalámbrico Dell con sensor óptico de alta precisión y batería de larga duración.',
        'price' => 29.99,
        'unit' => 'pcs',
        'qr_code' => 'QR-DEL004MOU',
        'status' => 1
    ],
    [
        'name' => 'Canon Impresora Multifuncional',
        'description' => 'Impresora Canon multifuncional con WiFi, impresión a color y escáner integrado.',
        'price' => 149.99,
        'unit' => 'unidad',
        'qr_code' => 'QR-CAN005IMP',
        'status' => 1
    ],
    [
        'name' => 'Sony Auriculares Bluetooth',
        'description' => 'Auriculares Sony con cancelación de ruido activa y 30 horas de batería.',
        'price' => 199.99,
        'unit' => 'par',
        'qr_code' => 'QR-SON006AUR',
        'status' => 1
    ],
    [
        'name' => 'LG Monitor Ultrawide 34"',
        'description' => 'Monitor LG ultrawide de 34 pulgadas, resolución 3440x1440, ideal para productividad.',
        'price' => 399.99,
        'unit' => 'unidad',
        'qr_code' => 'QR-LG007MONU',
        'status' => 1
    ],
    [
        'name' => 'Microsoft Surface Tablet',
        'description' => 'Tablet Microsoft Surface con Windows 11, pantalla táctil de 10.5 pulgadas.',
        'price' => 429.99,
        'unit' => 'pcs',
        'qr_code' => 'QR-MIC008TAB',
        'status' => 1
    ],
    [
        'name' => 'Asus Router WiFi 6',
        'description' => 'Router Asus con tecnología WiFi 6, cobertura hasta 3000 sq ft y 4 puertos Gigabit.',
        'price' => 129.99,
        'unit' => 'unidad',
        'qr_code' => 'QR-ASU009ROU',
        'status' => 1
    ],
    [
        'name' => 'Epson Escáner Profesional',
        'description' => 'Escáner Epson de alta resolución para documentos y fotos, con alimentador automático.',
        'price' => 179.99,
        'unit' => 'unidad',
        'qr_code' => 'QR-EPS010ESC',
        'status' => 1
    ],
    [
        'name' => 'Lenovo ThinkPad X1 Carbon',
        'description' => 'Laptop empresarial Lenovo ThinkPad con Intel i5, 8GB RAM, diseño ultraliviano.',
        'price' => 1099.99,
        'unit' => 'pcs',
        'qr_code' => 'QR-LEN011LAP',
        'status' => 1
    ],
    [
        'name' => 'Seagate Disco Duro Externo 2TB',
        'description' => 'Disco duro externo Seagate de 2TB, USB 3.0, ideal para backup y almacenamiento.',
        'price' => 79.99,
        'unit' => 'unidad',
        'qr_code' => 'QR-SEA012HDD',
        'status' => 1
    ],
    [
        'name' => 'Kingston Memoria USB 64GB',
        'description' => 'Memoria USB Kingston de 64GB, USB 3.1, velocidad de transferencia rápida.',
        'price' => 19.99,
        'unit' => 'pcs',
        'qr_code' => 'QR-KIN013USB',
        'status' => 1
    ],
    [
        'name' => 'Belkin Cable HDMI 4K',
        'description' => 'Cable HDMI Belkin de 2 metros, soporte 4K Ultra HD y HDR.',
        'price' => 24.99,
        'unit' => 'pcs',
        'qr_code' => 'QR-BEL014CAB',
        'status' => 1
    ],
    [
        'name' => 'Herman Miller Silla Ergonómica',
        'description' => 'Silla de oficina Herman Miller con soporte lumbar ajustable y materiales premium.',
        'price' => 599.99,
        'unit' => 'unidad',
        'qr_code' => 'QR-HER015SIL',
        'status' => 1
    ],
    [
        'name' => 'IKEA Escritorio Ajustable',
        'description' => 'Escritorio IKEA con altura ajustable, superficie de 120x60cm, ideal para trabajo.',
        'price' => 299.99,
        'unit' => 'unidad',
        'qr_code' => 'QR-IKE016ESC',
        'status' => 1
    ],
    [
        'name' => 'Philips Lámpara LED Escritorio',
        'description' => 'Lámpara LED Philips con brazo articulado, control de intensidad y temperatura de color.',
        'price' => 49.99,
        'unit' => 'unidad',
        'qr_code' => 'QR-PHI017LAM',
        'status' => 1
    ],
    [
        'name' => 'Casio Calculadora Científica',
        'description' => 'Calculadora científica Casio con 417 funciones, ideal para ingeniería y matemáticas.',
        'price' => 39.99,
        'unit' => 'pcs',
        'qr_code' => 'QR-CAS018CAL',
        'status' => 1
    ],
    [
        'name' => 'Netgear Switch 8 Puertos',
        'description' => 'Switch Netgear de 8 puertos Gigabit Ethernet, plug and play, carcasa metálica.',
        'price' => 69.99,
        'unit' => 'unidad',
        'qr_code' => 'QR-NET019SWI',
        'status' => 1
    ]
];

echo "🚀 Creando " . count($sampleItems) . " items de muestra...\n\n";

// Mostrar los items que se van a crear
foreach ($sampleItems as $index => $item) {
    $num = $index + 1;
    echo "📦 {$num}. {$item['name']}\n";
    echo "   💰 Precio: \${$item['price']} | 📏 Unidad: {$item['unit']} | 🏷️ QR: {$item['qr_code']}\n";
    echo "   📝 {$item['description']}\n\n";
}

echo "✅ Lista de " . count($sampleItems) . " items preparada.\n";
echo "💡 Para crear estos items en la base de datos, ejecuta:\n";
echo "   php artisan items:create-samples " . count($sampleItems) . "\n";
echo "   o usa el script completo: php scripts/create_sample_items.php " . count($sampleItems) . "\n\n";
echo "🎯 Estos items te permitirán probar:\n";
echo "   • Búsqueda por nombre: 'Samsung', 'HP', 'Apple'\n";
echo "   • Búsqueda por tipo: 'Laptop', 'Monitor', 'Mouse'\n";
echo "   • Búsqueda por código QR: 'QR-SAM', 'QR-HP'\n";
echo "   • Navegación con teclado y selección\n";
echo "   • Edición de cantidades inline\n\n";
echo "🎉 ¡Disfruta probando el nuevo selector de búsqueda!\n";
