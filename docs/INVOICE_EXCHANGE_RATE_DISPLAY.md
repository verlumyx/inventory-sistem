# 💱 Visualización de Precios con Tasa de Cambio en Facturas

## 🎯 Funcionalidad Implementada

Se implementó la visualización de precios en **bolívares** cuando la factura tiene una tasa de cambio diferente de 1, en las vistas de **Crear**, **Editar** y **Mostrar** facturas.

## ✅ Comportamiento

### **Condición de Activación**
- **Con tasa:** Cuando `rate != 1.0000` → Precios se muestran en **Bs** (bolívares)
- **Sin tasa:** Cuando `rate == 1.0000` → Precios se muestran en **$** (dólares)

### **Cálculos Aplicados**
- **Precio unitario:** `precio_usd × tasa = precio_bs`
- **Subtotal:** `subtotal_usd × tasa = subtotal_bs`
- **Total:** `total_usd × tasa = total_bs`

## 📋 Vistas Actualizadas

### **1. Vista Mostrar (`Show.tsx`)**
```tsx
// Precio unitario
{invoice.should_show_rate 
    ? `Bs ${(invoiceItem.price * invoice.rate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
    : invoiceItem.formatted_price
}

// Subtotal
{invoice.should_show_rate 
    ? `Bs ${(invoiceItem.subtotal * invoice.rate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
    : invoiceItem.formatted_subtotal
}
```

### **2. Vista Crear (`Create.tsx`)**
```tsx
// Precio unitario
{shouldShowRate 
    ? `Bs ${(invoiceItem.price * currentRate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
    : formatCurrency(invoiceItem.price)
}

// Subtotal
{shouldShowRate 
    ? `Bs ${(invoiceItem.amount * invoiceItem.price * currentRate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
    : formatCurrency(invoiceItem.amount * invoiceItem.price)
}

// Total
{shouldShowRate 
    ? `Bs ${(calculateTotal() * currentRate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
    : formatCurrency(calculateTotal())
}
```

### **3. Vista Editar (`Edit.tsx`)**
```tsx
// Precio unitario
{shouldShowRate 
    ? `Bs ${(invoiceItem.price * currentRate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
    : formatCurrency(invoiceItem.price)
}

// Subtotal
{shouldShowRate 
    ? `Bs ${((invoiceItem.subtotal || (invoiceItem.amount * invoiceItem.price)) * currentRate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
    : formatCurrency(invoiceItem.subtotal || (invoiceItem.amount * invoiceItem.price))
}

// Total
{shouldShowRate 
    ? `Bs ${(calculateTotal() * currentRate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
    : formatCurrency(calculateTotal())
}
```

## 🎨 Formato Visual

### **Con Tasa de Cambio (rate = 36.5000)**
```
┌─────────────────────────────────────────────────────────────────┐
│ Items de la Factura (2)                                         │
├─────────────────┬──────────┬─────────────┬─────────────┬────────┤
│ Item            │ Cantidad │ Precio      │ Subtotal    │ Acciones│
├─────────────────┼──────────┼─────────────┼─────────────┼────────┤
│ Laptop Dell     │ 2.00     │ Bs 18,250.00│ Bs 36,500.00│ [Edit] │
│ IT-001 • pcs    │          │             │             │        │
├─────────────────┼──────────┼─────────────┼─────────────┼────────┤
│ Mouse Logitech  │ 1.00     │ Bs 912.50   │ Bs 912.50   │ [Edit] │
│ IT-002 • pcs    │          │             │             │        │
├─────────────────┴──────────┴─────────────┼─────────────┼────────┤
│                              TOTAL:      │ Bs 37,412.50│        │
└───────────────────────────────────────────┴─────────────┴────────┘
```

### **Sin Tasa de Cambio (rate = 1.0000)**
```
┌─────────────────────────────────────────────────────────────────┐
│ Items de la Factura (2)                                         │
├─────────────────┬──────────┬─────────────┬─────────────┬────────┤
│ Item            │ Cantidad │ Precio      │ Subtotal    │ Acciones│
├─────────────────┼──────────┼─────────────┼─────────────┼────────┤
│ Laptop Dell     │ 2.00     │ $500.00     │ $1,000.00   │ [Edit] │
│ IT-001 • pcs    │          │             │             │        │
├─────────────────┼──────────┼─────────────┼─────────────┼────────┤
│ Mouse Logitech  │ 1.00     │ $25.00      │ $25.00      │ [Edit] │
│ IT-002 • pcs    │          │             │             │        │
├─────────────────┴──────────┴─────────────┼─────────────┼────────┤
│                              TOTAL:      │ $1,025.00   │        │
└───────────────────────────────────────────┴─────────────┴────────┘
```

## 🔧 Detalles Técnicos

### **Formato de Números**
```javascript
// Formato venezolano con separadores de miles y 2 decimales
(precio * tasa).toLocaleString('es-VE', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
})
```

### **Variables Utilizadas**

#### **Vista Show**
- `invoice.should_show_rate` - Boolean que indica si mostrar tasa
- `invoice.rate` - Valor numérico de la tasa de cambio

#### **Vistas Create y Edit**
- `shouldShowRate` - Boolean que indica si mostrar tasa
- `currentRate` - Valor numérico de la tasa de cambio actual

### **Campos Afectados**
1. **Precio unitario** de cada item
2. **Subtotal** de cada item  
3. **Total general** de la factura

## 🎯 Casos de Uso

### **Ejemplo 1: Factura con Tasa**
- **Item:** Laptop Dell - $500.00
- **Tasa:** 36.5000
- **Precio mostrado:** Bs 18,250.00
- **Cantidad:** 2
- **Subtotal mostrado:** Bs 36,500.00

### **Ejemplo 2: Factura sin Tasa**
- **Item:** Laptop Dell - $500.00
- **Tasa:** 1.0000
- **Precio mostrado:** $500.00
- **Cantidad:** 2
- **Subtotal mostrado:** $1,000.00

## 🚀 Beneficios

1. **Transparencia:** El usuario ve el precio real en la moneda local
2. **Consistencia:** Todos los precios en la misma moneda
3. **Automático:** Se activa automáticamente según la tasa
4. **Preciso:** Usa la tasa exacta de la factura
5. **Visual:** Formato claro con separadores de miles

## 📝 Notas Importantes

- Los **precios originales** en la base de datos permanecen en dólares
- La **conversión es solo visual** para la interfaz de usuario
- La **tasa se mantiene** fija por factura para consistencia
- El **formato venezolano** se usa para los números en bolívares
- La **lógica es consistente** en las tres vistas (Create, Edit, Show)

## 🔍 Verificación

Para verificar que funciona correctamente:

1. **Crear factura con tasa ≠ 1:** Los precios deben mostrarse en Bs
2. **Crear factura con tasa = 1:** Los precios deben mostrarse en $
3. **Editar factura existente:** Debe respetar la tasa original
4. **Ver factura:** Debe mostrar precios según su tasa configurada
