# 💱 Impresión con Tasa de Cambio

Esta guía explica cómo funciona la impresión de facturas cuando hay una tasa de cambio configurada.

## 🎯 Problema Resuelto

Cuando una factura tiene una tasa de cambio diferente de 1.0000, los precios de los items deben mostrarse en bolívares (Bs) en lugar de dólares ($) para reflejar el precio real que pagó el cliente.

## ⚙️ Funcionamiento Automático

El sistema detecta automáticamente si una factura tiene tasa de cambio y ajusta la visualización:

### 📊 Lógica de Detección

```php
// La factura tiene tasa si es diferente de 1.0000
$invoice->should_show_rate  // true si rate != 1.0000
```

### 💰 Conversión de Precios

**Cuando hay tasa de cambio:**
- Precio en Bs = Precio en USD × Tasa
- Subtotal en Bs = Subtotal en USD × Tasa

## 📋 Ejemplos Visuales

### Sin Tasa de Cambio (rate = 1.0000)

```
================================
        Mi Empresa C.A.
      RIF: J-12345678-9
    Calle Principal #123
        +58 412-123-4567
================================
            FACTURA
No: FV-00000001
Fecha: 21/08/2025 14:30
Almacen: Almacen Principal
--------------------------------
Laptop HP ProBook
2.00 x $500.00        $1,000.00

Mouse Logitech
1.00 x $25.00            $25.00
--------------------------------
                TOTAL: $1,025.00
================================
      Gracias por su compra!
```

### Con Tasa de Cambio (rate = 36.5000)

```
================================
        Mi Empresa C.A.
      RIF: J-12345678-9
    Calle Principal #123
        +58 412-123-4567
================================
            FACTURA
No: FV-00000001
Fecha: 21/08/2025 14:30
Almacen: Almacen Principal
--------------------------------
Laptop HP ProBook
2.00 x Bs 18,250.00  Bs 36,500.00

Mouse Logitech
1.00 x Bs 912.50        Bs 912.50
--------------------------------
            TOTAL Bs: 37,412.50
         total ref: $1,025.00
Tasa: 36.5000
================================
      Gracias por su compra!
```

## 🔍 Diferencias Clave

| Aspecto | Sin Tasa | Con Tasa |
|---------|----------|----------|
| **Precios de Items** | `$500.00` | `Bs 18,250.00` |
| **Subtotales** | `$1,000.00` | `Bs 36,500.00` |
| **Total Principal** | `TOTAL: $1,025.00` | `TOTAL Bs: 37,412.50` |
| **Total de Referencia** | No se muestra | `total ref: $1,025.00` |
| **Tasa** | No se muestra | `Tasa: 36.5000` |

## 🧮 Cálculos de Ejemplo

**Producto:** Laptop HP ProBook  
**Cantidad:** 2.00  
**Precio USD:** $500.00  
**Tasa:** 36.5000  

### Conversiones:
- **Precio en Bs:** $500.00 × 36.5000 = Bs 18,250.00
- **Subtotal en Bs:** 2.00 × Bs 18,250.00 = Bs 36,500.00

## 💡 Ventajas del Sistema

1. **Transparencia:** El cliente ve el precio real que pagó en bolívares
2. **Referencia:** Mantiene el precio original en dólares para referencia
3. **Automático:** No requiere configuración manual
4. **Consistente:** Todos los precios se muestran en la misma moneda

## 🔧 Configuración

### Establecer Tasa en Factura

```php
// Al crear la factura
$invoice = Invoice::create([
    'warehouse_id' => 1,
    'rate' => 36.5000, // Tasa del día
]);

// La factura automáticamente detectará que debe mostrar precios en Bs
echo $invoice->should_show_rate; // true
```

### Verificar Comportamiento

```php
// Verificar si una factura tiene tasa
if ($invoice->should_show_rate) {
    echo "Precios se mostrarán en bolívares";
} else {
    echo "Precios se mostrarán en dólares";
}
```

## 🧪 Testing

Los tests verifican que:

1. **Con tasa:** Los precios se muestran en bolívares
2. **Sin tasa:** Los precios se muestran en dólares
3. **Cálculos correctos:** Las conversiones son precisas

```bash
# Ejecutar tests específicos
php artisan test --filter="exchange_rate"
```

## 📝 Notas Técnicas

- Los precios originales en la base de datos permanecen en dólares
- La conversión se hace solo para la visualización
- La tasa se almacena con 4 decimales de precisión
- El total principal siempre se muestra en la moneda local (Bs)
- El total de referencia se muestra en dólares para comparación

## 🚀 Casos de Uso

1. **Negocio en Venezuela:** Precios en dólares, cobro en bolívares
2. **Control de cambio:** Registro de la tasa del día de la venta
3. **Auditoría:** Trazabilidad de las tasas utilizadas
4. **Reportes:** Comparación entre monedas
