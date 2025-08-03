-- Script SQL para insertar items de muestra
-- Ejecutar con: mysql -u usuario -p base_de_datos < database/sql/sample_items.sql

-- Insertar 20 items de muestra
INSERT INTO items (code, name, description, price, unit, qr_code, status, created_at, updated_at) VALUES
('IT-00000001', 'Samsung Laptop Galaxy Pro', 'Laptop Samsung con procesador Intel i7, 16GB RAM, SSD 512GB. Ideal para trabajo profesional y tareas exigentes.', 1299.99, 'pcs', 'QR-SAM001LP', 1, NOW(), NOW()),

('IT-00000002', 'HP Monitor 24 Pulgadas', 'Monitor HP Full HD de 24 pulgadas con tecnología IPS y conectividad HDMI. Perfecto para diseño gráfico y productividad.', 189.99, 'unidad', 'QR-HP024MON', 1, NOW(), NOW()),

('IT-00000003', 'Apple MacBook Air M2', 'MacBook Air con chip M2, 8GB RAM, SSD 256GB. Ultraligero y potente con gran autonomía de batería.', 1199.00, 'pcs', 'QR-APL002MBA', 1, NOW(), NOW()),

('IT-00000004', 'Logitech Teclado Mecánico MX', 'Teclado mecánico Logitech con switches Cherry MX y retroiluminación RGB. Diseño ergonómico para mayor comodidad.', 89.99, 'pcs', 'QR-LOG003TEC', 1, NOW(), NOW()),

('IT-00000005', 'Dell Mouse Inalámbrico Pro', 'Mouse inalámbrico Dell con sensor óptico de alta precisión y batería de larga duración. Diseño ergonómico.', 29.99, 'pcs', 'QR-DEL004MOU', 1, NOW(), NOW()),

('IT-00000006', 'Canon Impresora Multifuncional', 'Impresora Canon multifuncional con WiFi, impresión a color y escáner integrado. Ideal para oficina.', 149.99, 'unidad', 'QR-CAN005IMP', 1, NOW(), NOW()),

('IT-00000007', 'Sony Auriculares Bluetooth WH', 'Auriculares Sony con cancelación de ruido activa y 30 horas de batería. Calidad de sonido premium.', 199.99, 'par', 'QR-SON006AUR', 1, NOW(), NOW()),

('IT-00000008', 'LG Monitor Ultrawide 34"', 'Monitor LG ultrawide de 34 pulgadas, resolución 3440x1440, ideal para productividad y gaming.', 399.99, 'unidad', 'QR-LG007MONU', 1, NOW(), NOW()),

('IT-00000009', 'Microsoft Surface Tablet Pro', 'Tablet Microsoft Surface con Windows 11, pantalla táctil de 10.5 pulgadas y stylus incluido.', 429.99, 'pcs', 'QR-MIC008TAB', 1, NOW(), NOW()),

('IT-00000010', 'Asus Router WiFi 6 AX6000', 'Router Asus con tecnología WiFi 6, cobertura hasta 3000 sq ft y 4 puertos Gigabit Ethernet.', 129.99, 'unidad', 'QR-ASU009ROU', 1, NOW(), NOW()),

('IT-00000011', 'Epson Escáner Profesional V600', 'Escáner Epson de alta resolución para documentos y fotos, con alimentador automático de documentos.', 179.99, 'unidad', 'QR-EPS010ESC', 1, NOW(), NOW()),

('IT-00000012', 'Lenovo ThinkPad X1 Carbon', 'Laptop empresarial Lenovo ThinkPad con Intel i5, 8GB RAM, diseño ultraliviano y teclado retroiluminado.', 1099.99, 'pcs', 'QR-LEN011LAP', 1, NOW(), NOW()),

('IT-00000013', 'Seagate Disco Duro Externo 2TB', 'Disco duro externo Seagate de 2TB, USB 3.0, ideal para backup y almacenamiento de archivos grandes.', 79.99, 'unidad', 'QR-SEA012HDD', 1, NOW(), NOW()),

('IT-00000014', 'Kingston Memoria USB 64GB', 'Memoria USB Kingston de 64GB, USB 3.1, velocidad de transferencia rápida y diseño compacto.', 19.99, 'pcs', 'QR-KIN013USB', 1, NOW(), NOW()),

('IT-00000015', 'Belkin Cable HDMI 4K Premium', 'Cable HDMI Belkin de 2 metros, soporte 4K Ultra HD y HDR. Conectores chapados en oro.', 24.99, 'pcs', 'QR-BEL014CAB', 1, NOW(), NOW()),

('IT-00000016', 'Herman Miller Silla Ergonómica', 'Silla de oficina Herman Miller con soporte lumbar ajustable y materiales premium. Garantía 12 años.', 599.99, 'unidad', 'QR-HER015SIL', 1, NOW(), NOW()),

('IT-00000017', 'IKEA Escritorio Ajustable Bekant', 'Escritorio IKEA con altura ajustable, superficie de 120x60cm, ideal para trabajo y estudio.', 299.99, 'unidad', 'QR-IKE016ESC', 1, NOW(), NOW()),

('IT-00000018', 'Philips Lámpara LED Escritorio', 'Lámpara LED Philips con brazo articulado, control de intensidad y temperatura de color ajustable.', 49.99, 'unidad', 'QR-PHI017LAM', 1, NOW(), NOW()),

('IT-00000019', 'Casio Calculadora Científica FX', 'Calculadora científica Casio con 417 funciones, ideal para ingeniería, matemáticas y ciencias.', 39.99, 'pcs', 'QR-CAS018CAL', 1, NOW(), NOW()),

('IT-00000020', 'Netgear Switch 8 Puertos GS108', 'Switch Netgear de 8 puertos Gigabit Ethernet, plug and play, carcasa metálica resistente.', 69.99, 'unidad', 'QR-NET019SWI', 1, NOW(), NOW());

-- Mostrar estadísticas después de la inserción
SELECT 
    COUNT(*) as total_items,
    COUNT(CASE WHEN status = 1 THEN 1 END) as active_items,
    COUNT(CASE WHEN qr_code IS NOT NULL THEN 1 END) as items_with_qr,
    COUNT(CASE WHEN description IS NOT NULL THEN 1 END) as items_with_description
FROM items;
