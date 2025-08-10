# Ajustes de Inventario

Estructura creada siguiendo el módulo de Invoices/Entries.

- Backend: `app/Inventory/Adjustments`
- Frontend: `resources/js/pages/adjustments`
- Rutas: `routes/adjustments.php` (incluidas en `routes/web.php`)
- Provider: `App\Inventory\Adjustments\Providers\AdjustmentServiceProvider` (registrado en `bootstrap/providers.php`)

Modelo principal: Adjustment
- id
- code (autogenerado: AJ-00000001)
- description (nullable)
- warehouse_id
- status (0=Pendiente, 1=Aplicado)
- timestamps

Detalle (AdjustmentItem):
- id
- adjustment_id
- item_id
- amount (cantidad, decimal)
- timestamps

Pendiente: persistencia de items en UpdateHandler (se define según reglas de negocio de inventario si afecta stock al aplicar).

