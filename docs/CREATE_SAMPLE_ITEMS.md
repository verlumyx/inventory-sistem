# 📦 Crear Items de Muestra

Este documento explica cómo crear items de muestra para probar el selector de búsqueda mejorado en facturas y entradas.

## 🚀 Métodos Disponibles

### 1. Comando Artisan (Recomendado)

```bash
php artisan items:create-samples
```

**Opciones:**
- `count`: Número de items a crear (por defecto: 20)

**Ejemplos:**
```bash
# Crear 20 items (por defecto)
php artisan items:create-samples

# Crear 50 items
php artisan items:create-samples 50

# Crear 10 items
php artisan items:create-samples 10
```

### 2. Script PHP Directo

```bash
php scripts/create_sample_items.php [cantidad]
```

**Ejemplos:**
```bash
# Crear 20 items (por defecto)
php scripts/create_sample_items.php

# Crear 30 items
php scripts/create_sample_items.php 30
```

### 3. Seeder de Base de Datos

```bash
php artisan db:seed --class=ItemsSeeder
```

## 📊 Qué se Crea

Cada método creará items con las siguientes características:

### ✨ Características de los Items

- **Estado**: Todos activos (`status = true`)
- **Código QR**: Todos tendrán código QR generado
- **Descripción**: Todos tendrán descripción detallada
- **Precios**: Precios realistas según el tipo de item
- **Unidades**: Unidades apropiadas (pcs, unidad, kg, etc.)

### 🏷️ Tipos de Items Generados

El factory genera items variados incluyendo:

**Electrónicos:**
- Laptops, Monitores, Tablets, Smartphones
- Teclados, Mouse, Auriculares, Altavoces
- Impresoras, Escáneres, Cámaras, Proyectores

**Tecnología:**
- Discos Duros, Memorias USB, Cables, Adaptadores
- Routers, Switches, Servidores

**Oficina:**
- Sillas, Mesas, Escritorios, Archivadores
- Lámparas, Calculadoras, Teléfonos

**Marcas Incluidas:**
- Samsung, Apple, HP, Dell, Lenovo
- Canon, Epson, Microsoft, Logitech
- Sony, LG, Asus, Acer, Generic

## 📈 Información Mostrada

Después de la creación, verás:

```
✅ Se han creado 20 items exitosamente.

📊 Estadísticas del inventario:
   • Total de items: 25
   • Items activos: 23
   • Items con QR: 20
   • Items con descripción: 20

📦 Ejemplos de items creados:
   • IT-00000006 - Samsung Laptop Pro
     Precio: $1,245.67 | Unidad: pcs | QR: QR-AB12CD34
   • IT-00000007 - HP Monitor Plus
     Precio: $345.99 | Unidad: unidad | QR: QR-EF56GH78
   ... y 15 items más

🎉 ¡Listo! Ahora puedes probar el selector de búsqueda con más items.
```

## 🎯 Cómo Probar

Una vez creados los items:

1. **Ve a Facturas → Crear Nueva Factura**
2. **En la sección "Agregar Items"**
3. **Click en el campo "Item"**
4. **Escribe para buscar:**
   - Por nombre: "Samsung", "Laptop", "Monitor"
   - Por código: "IT-", "00000"
   - Por marca: "HP", "Dell", "Apple"

## 🔍 Funcionalidades a Probar

### Búsqueda Inteligente
- ✅ Buscar por nombre parcial
- ✅ Buscar por código
- ✅ Buscar por marca
- ✅ Navegación con teclado (↑↓)
- ✅ Selección con Enter
- ✅ Cancelar con Escape

### Edición de Cantidad
- ✅ Click en botón de editar (✏️)
- ✅ Cambiar cantidad
- ✅ Guardar con Enter o ✅
- ✅ Cancelar con Escape o ❌

## 🛠️ Solución de Problemas

### Error: "php: command not found"
Si estás en un entorno donde `php` no está en el PATH:
- Usa el path completo: `/usr/bin/php` o `/opt/homebrew/bin/php`
- O configura tu PATH correctamente

### Error de Base de Datos
Asegúrate de que:
- La base de datos esté configurada correctamente
- Las migraciones estén ejecutadas
- El archivo `.env` tenga la configuración correcta

### Error de Permisos
```bash
chmod +x scripts/create_sample_items.php
```

## 📝 Notas

- Los códigos de items se generan automáticamente (IT-00000001, IT-00000002, etc.)
- Los precios son realistas según el tipo de producto
- Las descripciones son detalladas y profesionales
- Los códigos QR siguen el formato QR-XXXXXXXX

¡Disfruta probando el nuevo selector de búsqueda! 🎉
