# 📊 Sistema de Importación y Exportación de Artículos

## ✨ Funcionalidades Implementadas

### **🔽 Descargar Plantilla**
- **Botón**: "Descargar Plantilla" en el listado de artículos
- **Archivo**: `plantilla_articulos.xlsx`
- **Contenido**: Plantilla Excel con formato correcto y ejemplos

### **🔼 Importar Plantilla**
- **Botón**: "Importar Plantilla" en el listado de artículos
- **Modal**: Interfaz para seleccionar archivo
- **Validación**: Verificación de formato y datos

## 🎯 Flujo de Trabajo

### **1. Descargar Plantilla**
```
Usuario → Clic "Descargar Plantilla" → Descarga automática de Excel
```

### **2. Completar Plantilla**
```
Usuario → Abre Excel → Completa datos → Guarda archivo
```

### **3. Importar Datos**
```
Usuario → Clic "Importar Plantilla" → Selecciona archivo → Confirma importación
```

## 📋 Estructura de la Plantilla

### **Columnas Obligatorias**
- **Nombre**: Nombre descriptivo del artículo
- **Precio**: Precio en formato decimal

### **Columnas Opcionales**
- **Descripción**: Descripción detallada
- **Unidad**: Unidad de medida (pcs, kg, m, etc.)
- **Código de Barra**: Código de barras único
- **Estado**: "Activo" o "Inactivo"

### **Código Autogenerado**
- ✅ **Automático**: El sistema genera códigos únicos (IT-00000001, IT-00000002, etc.)
- ✅ **Sin duplicados**: No necesitas preocuparte por códigos únicos
- ✅ **Secuencial**: Basado en el último ID + 1

### **Ejemplo de Datos**
```
Nombre              | Descripción                    | Precio | Unidad | Código de Barra | Estado
Laptop Dell         | Laptop para oficina 8GB RAM   | 850.00 | pcs    | 1234567890123   | Activo
Mouse Inalámbrico   | Mouse ergonómico              | 25.50  | pcs    | 1234567890124   | Activo
```

## 🎨 Interfaz de Usuario

### **Botones en Header**
```tsx
<div className="flex gap-2">
    <Button variant="outline" onClick={handleDownloadTemplate}>
        <Download className="h-4 w-4 mr-2" />
        Descargar Plantilla
    </Button>
    <Button variant="outline" onClick={() => setShowImportModal(true)}>
        <Upload className="h-4 w-4 mr-2" />
        Importar Plantilla
    </Button>
    <Button asChild>
        <Link href="/items/create">
            <Plus className="h-4 w-4 mr-2" />
            Nuevo Artículo
        </Link>
    </Button>
</div>
```

### **Modal de Importación**
```tsx
<Dialog open={showImportModal} onOpenChange={setShowImportModal}>
    <DialogContent>
        <DialogHeader>
            <DialogTitle>Importar Artículos</DialogTitle>
            <DialogDescription>
                Selecciona un archivo Excel (.xlsx) con los artículos a importar.
            </DialogDescription>
        </DialogHeader>
        
        <Input type="file" accept=".xlsx,.xls" onChange={handleFileSelect} />
        
        <DialogFooter>
            <Button variant="outline" onClick={() => setShowImportModal(false)}>
                Cancelar
            </Button>
            <Button onClick={handleImportFile} disabled={!importFile}>
                Importar
            </Button>
        </DialogFooter>
    </DialogContent>
</Dialog>
```

## 🔧 Implementación Backend

### **Rutas Agregadas**
```php
// routes/items.php
Route::get('/download-template', [ImportExportController::class, 'downloadTemplate'])->name('download-template');
Route::post('/import', [ImportExportController::class, 'import'])->name('import');
```

### **Controlador**
```php
// app/Inventory/Item/Controllers/ImportExportController.php
class ImportExportController extends Controller
{
    public function downloadTemplate(): BinaryFileResponse
    {
        $filePath = $this->exportHandler->generateTemplate();
        return response()->download($filePath, 'plantilla_articulos.xlsx')->deleteFileAfterSend();
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:10240']);
        $result = $this->importHandler->handle($request->file('file'));
        return redirect()->route('items.index')->with('success', "Importados: {$result['imported']}");
    }
}
```

### **Handlers**

#### **ExportTemplateHandler**
- Genera archivo Excel con formato correcto
- Incluye headers estilizados
- Agrega ejemplos de datos
- Crea hoja de instrucciones

#### **ImportItemsHandler**
- Lee archivo Excel
- Valida formato y datos
- Verifica códigos únicos
- Crea artículos en lote
- Maneja errores individualmente

## ✅ Validaciones Implementadas

### **Validación de Archivo**
- ✅ **Formato**: Solo archivos .xlsx y .xls
- ✅ **Tamaño**: Máximo 10MB
- ✅ **Headers**: Verificación de columnas correctas

### **Validación de Datos**
- ✅ **Campos obligatorios**: Código, Nombre, Precio
- ✅ **Código único**: No duplicados en base de datos
- ✅ **Precio válido**: Número decimal mayor o igual a 0
- ✅ **Código de barra único**: Si se proporciona
- ✅ **Estado válido**: "Activo" o "Inactivo"

### **Manejo de Errores**
- ✅ **Errores por fila**: Identificación específica
- ✅ **Transacciones**: Rollback en caso de error crítico
- ✅ **Logging**: Registro detallado de errores
- ✅ **Feedback**: Mensajes claros al usuario

## 🎯 Casos de Uso

### **Caso 1: Importación Masiva Inicial**
```
Empresa nueva → Descarga plantilla → Completa 100+ artículos → Importa todo de una vez
```

### **Caso 2: Actualización Periódica**
```
Empresa existente → Descarga plantilla → Agrega nuevos artículos → Importa incrementalmente
```

### **Caso 3: Migración de Sistema**
```
Sistema anterior → Exporta datos → Adapta formato → Importa a nuevo sistema
```

## 🚀 Beneficios

### **Para Usuarios**
- ⚡ **Rapidez**: Importar cientos de artículos en segundos
- 🎯 **Precisión**: Plantilla con formato correcto
- 🔄 **Flexibilidad**: Campos opcionales y obligatorios
- 📊 **Feedback**: Reporte detallado de importación

### **Para Administradores**
- 📈 **Escalabilidad**: Manejo de grandes volúmenes
- 🛡️ **Seguridad**: Validaciones exhaustivas
- 📝 **Trazabilidad**: Logging completo
- 🔧 **Mantenimiento**: Código modular y extensible

## 🎮 Cómo Usar

### **1. Descargar Plantilla**
1. Ve a **Artículos**
2. Haz clic en **"Descargar Plantilla"**
3. Se descarga automáticamente `plantilla_articulos.xlsx`

### **2. Completar Datos**
1. Abre el archivo Excel descargado
2. Ve a la hoja **"Plantilla Artículos"**
3. **Elimina las filas de ejemplo**
4. Completa tus datos siguiendo el formato
5. Guarda el archivo

### **3. Importar Datos**
1. Ve a **Artículos**
2. Haz clic en **"Importar Plantilla"**
3. Selecciona tu archivo completado
4. Haz clic en **"Importar"**
5. Revisa el mensaje de confirmación

## 📋 Checklist de Implementación

- ✅ **Frontend**: Botones y modal implementados
- ✅ **Backend**: Rutas y controladores creados
- ✅ **Handlers**: Lógica de exportación e importación
- ✅ **Validaciones**: Verificaciones exhaustivas
- ✅ **Plantilla**: Excel con formato y ejemplos
- ✅ **Documentación**: Guía completa de uso

## 🔮 Próximas Mejoras

### **Funcionalidades Adicionales**
- [ ] **Vista previa**: Mostrar datos antes de importar
- [ ] **Importación parcial**: Seleccionar filas específicas
- [ ] **Plantillas personalizadas**: Diferentes formatos
- [ ] **Exportación de datos**: Descargar artículos existentes

### **Optimizaciones**
- [ ] **Procesamiento asíncrono**: Para archivos muy grandes
- [ ] **Barra de progreso**: Feedback visual durante importación
- [ ] **Validación en tiempo real**: Verificar datos mientras se escriben
- [ ] **Historial de importaciones**: Registro de operaciones anteriores

¡Disfruta de la nueva funcionalidad de importación masiva! 🎉
