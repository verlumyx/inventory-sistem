# 📊 Dashboard: Items con Menor Disponibilidad

## 🎯 Funcionalidad Implementada

Se agregó una nueva sección al dashboard que muestra **los 10 items con menor disponibilidad** en tiempo real.

## ✅ Características

### **📋 Información Mostrada**
- **Top 10** items con menor stock
- **Nombre** del item
- **Código** del item  
- **Precio** formateado
- **Unidad** de medida
- **Stock total** (suma de todos los almacenes)
- **Estado del stock** (Sin Stock, Stock Bajo, En Stock)

### **🎨 Interfaz Visual**
- **Numeración** del 1 al 10
- **Colores indicativos:**
  - 🔴 **Rojo**: Sin stock (0 unidades)
  - 🟡 **Amarillo**: Stock bajo (≤ 5 unidades)
  - 🟢 **Verde**: En stock (> 5 unidades)
- **Iconos** de alerta para items sin stock
- **Diseño responsivo** con cards

### **⚡ Datos en Tiempo Real**
- Se actualiza cada vez que se accede al dashboard
- Incluye **solo items activos**
- **Suma automática** del stock de todos los almacenes
- **Ordenamiento** por menor disponibilidad

## 🔧 Implementación Técnica

### **Backend (PHP)**

#### **Controlador: `DashboardController.php`**
```php
private function getLowStockItems(): array
{
    $items = \App\Inventory\Item\Models\Item::select([
            'items.id', 'items.code', 'items.name', 
            'items.price', 'items.unit'
        ])
        ->join('warehouse_items', 'items.id', '=', 'warehouse_items.item_id')
        ->selectRaw('SUM(warehouse_items.quantity_available) as total_stock')
        ->where('items.status', true) // Solo items activos
        ->groupBy('items.id', 'items.code', 'items.name', 'items.price', 'items.unit')
        ->orderBy('total_stock', 'asc') // Menor stock primero
        ->limit(10)
        ->get();

    return $items->map(function ($item) {
        return [
            'id' => $item->id,
            'code' => $item->code,
            'name' => $item->name,
            'price' => $item->price,
            'unit' => $item->unit,
            'total_stock' => (float) $item->total_stock,
            'stock_status' => $item->total_stock <= 0 ? 'Sin Stock' : 
                            ($item->total_stock <= 5 ? 'Stock Bajo' : 'En Stock')
        ];
    })->toArray();
}
```

#### **Query SQL Generada**
```sql
SELECT 
    items.id, items.code, items.name, items.price, items.unit,
    SUM(warehouse_items.quantity_available) as total_stock
FROM items 
INNER JOIN warehouse_items ON items.id = warehouse_items.item_id 
WHERE items.status = 1 
GROUP BY items.id, items.code, items.name, items.price, items.unit 
ORDER BY total_stock ASC 
LIMIT 10
```

### **Frontend (React/TypeScript)**

#### **Interfaz TypeScript**
```typescript
interface LowStockItem {
    id: number;
    code: string;
    name: string;
    price: number;
    unit: string;
    total_stock: number;
    stock_status: string;
}
```

#### **Componente React**
```tsx
<Card className="flex-1">
    <CardHeader>
        <CardTitle className="flex items-center gap-2">
            <TrendingDown className="h-5 w-5 text-red-600" />
            Items con Menor Disponibilidad
        </CardTitle>
    </CardHeader>
    <CardContent>
        {low_stock_items.map((item, index) => (
            <div key={item.id} className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <div className="w-8 h-8 rounded-full bg-muted">
                        {index + 1}
                    </div>
                    <div>
                        <div className="font-medium">{item.name}</div>
                        <div className="text-xs text-muted-foreground">
                            {item.code} • {formatCurrency(item.price)} • {item.unit}
                        </div>
                    </div>
                </div>
                <div className="text-right">
                    <div className="font-medium">{item.total_stock} {item.unit}</div>
                    <div className="text-xs">{item.stock_status}</div>
                </div>
            </div>
        ))}
    </CardContent>
</Card>
```

## 📍 Ubicación en el Dashboard

**Línea 84** del archivo `resources/js/pages/dashboard.tsx`

La sección reemplaza el área del chart que estaba vacía y se muestra debajo de las 3 tarjetas de estadísticas principales.

## 🎯 Casos de Uso

### **1. Control de Inventario**
- Identificar rápidamente items que necesitan reposición
- Priorizar compras basadas en disponibilidad
- Evitar quedarse sin stock de items importantes

### **2. Gestión de Almacén**
- Monitorear niveles críticos de inventario
- Planificar traslados entre almacenes
- Optimizar espacio de almacenamiento

### **3. Toma de Decisiones**
- Alertas visuales para items sin stock
- Información consolidada de todos los almacenes
- Datos actualizados en tiempo real

## 🔍 Ejemplo de Datos

```json
[
    {
        "id": 1,
        "code": "IT-00000001",
        "name": "Telefono",
        "price": 150.00,
        "unit": "pcs",
        "total_stock": 25.0,
        "stock_status": "En Stock"
    },
    {
        "id": 4,
        "code": "IT-00000004", 
        "name": "Harina",
        "price": 2.50,
        "unit": "kg",
        "total_stock": 90.0,
        "stock_status": "En Stock"
    }
]
```

## 🚀 Beneficios

1. **Visibilidad inmediata** de items con stock bajo
2. **Interfaz intuitiva** con colores y iconos
3. **Datos consolidados** de todos los almacenes
4. **Actualización automática** sin configuración adicional
5. **Integración perfecta** con el dashboard existente

## 🔧 Mantenimiento

- **Sin configuración adicional** requerida
- **Actualización automática** con cada acceso al dashboard
- **Rendimiento optimizado** con query eficiente
- **Escalable** para cualquier cantidad de items y almacenes
