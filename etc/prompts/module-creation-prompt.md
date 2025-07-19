# Prompt para Creación de Módulos de Inventario

## Objetivo
Crear un módulo completo siguiendo exactamente la misma arquitectura y patrones del módulo de Warehouses/Items.

## Estructura del Prompt

### 1. Información Inicial Requerida
Solicitar al usuario:
- **Nombre del módulo** (singular y plural)
- **Propiedades del modelo** con tipos y validaciones
- **Código automático** (prefijo y formato)
- **Campos únicos** (si los hay)
- **Campos opcionales vs requeridos**
- **Si permite eliminación** (por defecto NO)

### 2. Estructura de Directorios Backend

Crear la siguiente estructura en `app/Inventory/{ModuleName}/`:

```
app/Inventory/{ModuleName}/
├── Models/
│   └── {ModelName}.php
├── Contracts/
│   └── {ModelName}RepositoryInterface.php
├── Repositories/
│   └── {ModelName}Repository.php
├── Handlers/
│   ├── List{ModuleName}Handler.php
│   ├── Create{ModelName}Handler.php
│   ├── Update{ModelName}Handler.php
│   └── Get{ModelName}Handler.php
├── Controllers/
│   ├── List{ModuleName}Controller.php
│   ├── Create{ModelName}Controller.php
│   ├── Update{ModelName}Controller.php
│   └── Get{ModelName}Controller.php
├── Requests/
│   ├── List{ModuleName}Request.php
│   ├── Create{ModelName}Request.php
│   ├── Update{ModelName}Request.php
│   └── Get{ModelName}Request.php
├── Exceptions/
│   ├── {ModelName}NotFoundException.php
│   ├── {ModelName}ValidationException.php
│   └── {ModelName}OperationException.php
└── Providers/
    └── {ModelName}ServiceProvider.php
```

### 3. Estructura Frontend

Crear la siguiente estructura en `resources/js/pages/{modulename}/`:

```
resources/js/pages/{modulename}/
├── Index.tsx
├── Create.tsx
├── Edit.tsx
└── Show.tsx
```

### 4. Rutas

Crear archivo `routes/{modulename}.php` con:
- Rutas web completas (GET, POST, PUT, PATCH)
- Rutas API adicionales para búsquedas
- Validaciones de parámetros
- Middleware de autenticación

### 5. Patrones de Implementación

#### 5.1 Modelo (Models/{ModelName}.php)
**Características obligatorias:**
- Usar `HasFactory` trait
- Tabla personalizada con nombre plural
- Campos `fillable` según propiedades
- Casts para `status` (boolean) y timestamps
- Método `boot()` para generar código automático
- Método `generateCode()` estático
- Accessor `getStatusTextAttribute()`
- Scopes: `active()`, `inactive()`, `search()`, `byStatus()`, `byName()`, `byCode()`
- Métodos de validación: `isCodeUnique()`, `findByCode()`
- Método `getFiltered()` estático
- Métodos de estado: `isActive()`, `isInactive()`, `activate()`, `deactivate()`, `toggleStatus()`
- Accessors: `getDisplayNameAttribute()`, `getShortDescriptionAttribute()`
- Método `toApiArray()` para respuestas API

#### 5.2 Migración
**Características obligatorias:**
- Campo `id` auto-incremental
- Campo `code` único con índice
- Campo `name` requerido con índice
- Campo `status` boolean default true con índice
- Timestamps con índice compuesto
- Campos específicos del módulo según propiedades
- Índices de rendimiento apropiados
- Comentarios descriptivos en columnas

#### 5.3 Repository Pattern
**Interface (Contracts/{ModelName}RepositoryInterface.php):**
- `getAll(array $filters = []): Collection`
- `getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator`
- `findById(int $id): ?{ModelName}`
- `findByCode(string $code): ?{ModelName}`
- `create(array $data): {ModelName}`
- `update({ModelName} $model, array $data): {ModelName}`
- `exists(int $id): bool`
- `isCodeUnique(string $code, ?int $excludeId = null): bool`
- `getActive(): Collection`
- `getInactive(): Collection`
- `count(array $filters = []): int`

**Implementación (Repositories/{ModelName}Repository.php):**
- Implementar todos los métodos de la interfaz
- Usar el modelo para operaciones
- Aplicar filtros y ordenamiento
- Manejo de errores apropiado

#### 5.4 Handlers (Casos de Uso)
**List{ModuleName}Handler.php:**
- `handleAll(array $filters = []): Collection`
- `handlePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator`
- Logging de operaciones
- Manejo de excepciones

**Create{ModelName}Handler.php:**
- `handle(array $data): {ModelName}`
- Validaciones de negocio
- Transacciones de base de datos
- Logging detallado
- Manejo de excepciones específicas

**Update{ModelName}Handler.php:**
- `handle(int $id, array $data): {ModelName}`
- Verificación de existencia
- Validaciones de unicidad
- Transacciones
- Logging y excepciones

**Get{ModelName}Handler.php:**
- `handleById(int $id): {ModelName}`
- `handleByCode(string $code): {ModelName}`
- Manejo de no encontrado
- Logging de accesos

#### 5.5 Controllers
**Características obligatorias:**
- Extender `Controller`
- Inyección de dependencias de handlers
- Métodos `__invoke()` para single-action controllers
- Retorno de respuestas Inertia
- Manejo completo de excepciones
- Redirecciones apropiadas
- Mensajes flash de éxito/error

#### 5.6 Requests (Validación)
**List{ModuleName}Request.php:**
- Validación de filtros de búsqueda
- Validación de paginación
- Validación de ordenamiento
- Preparación de datos con defaults

**Create{ModelName}Request.php:**
- Validación de campos requeridos
- Validación de unicidad
- Validación de formatos
- Mensajes personalizados en español
- Preparación y limpieza de datos

**Update{ModelName}Request.php:**
- Validación con exclusión de ID actual
- Validación condicional de campos
- Mismas reglas que Create pero con `sometimes`

#### 5.7 Excepciones
**{ModelName}NotFoundException.php:**
- Métodos estáticos: `byId()`, `byCode()`
- Código HTTP 404
- Mensajes descriptivos en español

**{ModelName}ValidationException.php:**
- Manejo de errores de validación
- Métodos estáticos para casos comunes
- Array de errores estructurado
- Código HTTP 422

**{ModelName}OperationException.php:**
- Errores de operaciones de negocio
- Código HTTP 500
- Logging automático

### 6. Frontend React Components

#### 6.1 Index.tsx
**Características obligatorias:**
- Listado con tabla responsive
- Filtros avanzados (búsqueda, estado, campos específicos)
- Paginación completa
- Botones de acción (Ver, Editar)
- Breadcrumbs dinámicos
- Manejo de estados de carga
- Mensajes flash
- Diseño consistente con Tailwind CSS

#### 6.2 Create.tsx
**Características obligatorias:**
- Formulario completo con todos los campos
- Validaciones del lado cliente
- Manejo de errores por campo
- Estados de carga
- Botones Cancelar/Guardar
- Breadcrumbs
- Información de ayuda para campos

#### 6.3 Edit.tsx
**Características obligatorias:**
- Pre-carga de datos existentes
- Misma estructura que Create
- Información de fechas del sistema
- Botón adicional "Ver Detalle"
- Validaciones de cambios

#### 6.4 Show.tsx
**Características obligatorias:**
- Vista detallada de solo lectura
- Información básica y metadatos
- Sidebar con acciones rápidas
- Información de estado y fechas
- Badges para estado
- Botones Editar (NO Eliminar por defecto)

### 7. Configuración y Registro

#### 7.1 Service Provider
- Registrar binding de Repository Interface
- Configurar en `bootstrap/providers.php`

#### 7.2 Rutas
- Archivo dedicado `routes/{modulename}.php`
- Incluir en `routes/web.php`
- Rutas web completas
- Rutas API adicionales para búsquedas

#### 7.3 Navegación
- Agregar al sidebar en `resources/js/components/app-sidebar.tsx`
- Icono apropiado de Lucide React
- Orden lógico en el menú

#### 7.4 Factory y Seeder
- Factory realista con datos variados
- Seeder con ejemplos específicos y datos aleatorios
- Registrar en `DatabaseSeeder.php`

### 8. Datos de Prueba

#### 8.1 Factory
**Características obligatorias:**
- Datos realistas según el dominio
- Variedad en los datos generados
- Estados para testing (activo/inactivo)
- Métodos de configuración (withX, withoutX)

#### 8.2 Seeder
**Características obligatorias:**
- Mezcla de datos aleatorios y específicos
- Ejemplos representativos del dominio
- Distribución realista de estados
- Logging de resultados

### 9. Checklist de Implementación

#### Backend ✅
- [ ] Estructura de directorios creada
- [ ] Modelo con todos los métodos requeridos
- [ ] Migración con índices y comentarios
- [ ] Repository Interface completa
- [ ] Repository Implementation
- [ ] Todos los Handlers implementados
- [ ] Todos los Controllers implementados
- [ ] Todos los Requests de validación
- [ ] Todas las Excepciones personalizadas
- [ ] Service Provider configurado
- [ ] Factory y Seeder creados

#### Frontend ✅
- [ ] Estructura de páginas creada
- [ ] Index.tsx con filtros y paginación
- [ ] Create.tsx con formulario completo
- [ ] Edit.tsx con pre-carga de datos
- [ ] Show.tsx con vista detallada
- [ ] Navegación agregada al sidebar

#### Configuración ✅
- [ ] Rutas web configuradas
- [ ] Rutas incluidas en web.php
- [ ] Service Provider registrado
- [ ] Migraciones ejecutadas
- [ ] Seeders ejecutados
- [ ] Navegación funcionando

#### Testing ✅
- [ ] Rutas listadas correctamente
- [ ] CRUD completo funcionando
- [ ] Validaciones trabajando
- [ ] Filtros y búsqueda operativos
- [ ] Paginación funcionando
- [ ] Mensajes flash mostrándose

### 10. Ejemplo de Uso del Prompt

```
Crear módulo: Suppliers (Proveedores)
Modelo: Supplier
Propiedades:
- id: auto-incremental
- code: autogenerado SUP-00000001, único
- name: string, requerido, máx 255
- email: string, único, opcional
- phone: string, opcional
- address: text, opcional
- contact_person: string, opcional
- status: boolean, default true
- created_at, updated_at: timestamps

Eliminación: NO permitida
```

### 11. Notas Importantes

- **NUNCA incluir funcionalidad de eliminación** a menos que se especifique explícitamente
- **Seguir exactamente** los patrones de Warehouses/Items
- **Mantener consistencia** en nombres, estructura y estilo
- **Usar español** para mensajes, comentarios y UI
- **Implementar logging** en todos los handlers
- **Validar unicidad** en campos únicos
- **Usar transacciones** en operaciones críticas
- **Manejar excepciones** apropiadamente en todos los niveles
- **Mantener responsive design** en frontend
- **Usar TypeScript** correctamente en React components

### 12. Patrones de Código Específicos

#### 12.1 Estructura de Namespaces
```php
// Modelo
namespace App\Inventory\{ModuleName}\Models;

// Repository
namespace App\Inventory\{ModuleName}\Repositories;
namespace App\Inventory\{ModuleName}\Contracts;

// Handlers
namespace App\Inventory\{ModuleName}\Handlers;

// Controllers
namespace App\Inventory\{ModuleName}\Controllers;

// Requests
namespace App\Inventory\{ModuleName}\Requests;

// Exceptions
namespace App\Inventory\{ModuleName}\Exceptions;
```

#### 12.2 Convenciones de Nombres
- **Archivos**: PascalCase (ej: CreateWarehouseHandler.php)
- **Clases**: PascalCase (ej: CreateWarehouseHandler)
- **Métodos**: camelCase (ej: handleById)
- **Variables**: camelCase (ej: $warehouseData)
- **Constantes**: UPPER_SNAKE_CASE
- **Rutas**: kebab-case (ej: /warehouses/create)
- **Componentes React**: PascalCase (ej: Index.tsx)

#### 12.3 Patrones de Logging
```php
// En Handlers - siempre incluir contexto relevante
Log::info('{Acción} exitosa', [
    '{model}_id' => $model->id,
    '{model}_code' => $model->code,
    'additional_context' => $value,
]);

Log::error('Error en {acción}', [
    'error' => $e->getMessage(),
    'context' => $context,
]);
```

#### 12.4 Estructura de Respuestas API
```php
// En Controllers - formato estándar
return Inertia::render('{moduleName}/Show', [
    '{modelname}' => [
        'id' => $model->id,
        'code' => $model->code,
        'name' => $model->name,
        // ... otros campos
        'status' => $model->status,
        'status_text' => $model->status_text,
        'created_at' => $model->created_at->toISOString(),
        'updated_at' => $model->updated_at->toISOString(),
    ]
]);
```

#### 12.5 Patrones de Validación
```php
// En Requests - estructura estándar
public function rules(): array
{
    return [
        'name' => [
            'required',
            'string',
            'max:255',
            'min:2',
        ],
        'code' => [
            'sometimes',
            'string',
            'max:20',
            'unique:{table},code',
            'regex:/^{PREFIX}-\d{8}$/',
        ],
        'status' => [
            'sometimes',
            'boolean',
        ],
    ];
}
```

### 13. Plantillas de Código Base

#### 13.1 Plantilla de Modelo
```php
<?php

namespace App\Inventory\{ModuleName}\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Database\Factories\{ModelName}Factory;

class {ModelName} extends Model
{
    use HasFactory;

    protected $table = '{table_name}';

    protected $fillable = [
        'code',
        'name',
        // ... campos específicos
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return {ModelName}Factory::new();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->code)) {
                $model->code = static::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $lastModel = static::orderBy('id', 'desc')->first();
        $nextNumber = $lastModel ? $lastModel->id + 1 : 1;

        return '{PREFIX}-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
    }

    // ... resto de métodos estándar
}
```

#### 13.2 Plantilla de Handler
```php
<?php

namespace App\Inventory\{ModuleName}\Handlers;

use App\Inventory\{ModuleName}\Contracts\{ModelName}RepositoryInterface;
use App\Inventory\{ModuleName}\Models\{ModelName};
use Illuminate\Support\Facades\Log;

class Create{ModelName}Handler
{
    public function __construct(
        private {ModelName}RepositoryInterface ${modelname}Repository
    ) {}

    public function handle(array $data): {ModelName}
    {
        try {
            // Validaciones de negocio

            // Crear modelo
            $model = $this->{modelname}Repository->create($data);

            Log::info('{ModelName} creado exitosamente', [
                '{modelname}_id' => $model->id,
                '{modelname}_code' => $model->code,
            ]);

            return $model;

        } catch (\Exception $e) {
            Log::error('Error al crear {modelname}', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
```

#### 13.3 Plantilla de Controller
```php
<?php

namespace App\Inventory\{ModuleName}\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\{ModuleName}\Handlers\Create{ModelName}Handler;
use App\Inventory\{ModuleName}\Requests\Create{ModelName}Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class Create{ModelName}Controller extends Controller
{
    public function __construct(
        private Create{ModelName}Handler $create{ModelName}Handler
    ) {}

    public function create(): Response
    {
        return Inertia::render('{ModuleName}/Create');
    }

    public function store(Create{ModelName}Request $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            $model = $this->create{ModelName}Handler->handle($validated);

            return redirect()
                ->route('{modulename}.show', $model->id)
                ->with('success', "{ModelName} '{$model->name}' creado exitosamente.");

        } catch (\Exception $e) {
            \Log::error('Error creating {modelname}: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['general' => 'Error al crear el {modelname}. Por favor, intente nuevamente.']);
        }
    }
}
```

### 14. Comandos de Generación Automática

#### 14.1 Secuencia de Comandos Laravel
```bash
# 1. Crear migración
php artisan make:migration create_{table_name}_table

# 2. Crear factory
php artisan make:factory {ModelName}Factory --model=App\\Inventory\\{ModuleName}\\Models\\{ModelName}

# 3. Crear seeder
php artisan make:seeder {ModelName}Seeder

# 4. Crear service provider
php artisan make:provider {ModelName}ServiceProvider

# 5. Ejecutar migraciones
php artisan migrate

# 6. Ejecutar seeders
php artisan db:seed --class={ModelName}Seeder
```

#### 14.2 Verificación de Rutas
```bash
# Listar rutas del módulo
php artisan route:list --path={modulename}

# Verificar rutas específicas
php artisan route:list --name={modulename}
```

### 15. Checklist de Calidad

#### 15.1 Código Backend
- [ ] Todos los archivos tienen namespace correcto
- [ ] Imports organizados alfabéticamente
- [ ] Documentación PHPDoc completa
- [ ] Manejo de excepciones en todos los niveles
- [ ] Logging apropiado en handlers
- [ ] Validaciones de negocio implementadas
- [ ] Transacciones en operaciones críticas
- [ ] Códigos HTTP correctos en respuestas

#### 15.2 Código Frontend
- [ ] Imports organizados por tipo (React, Inertia, UI, Icons)
- [ ] Interfaces TypeScript definidas
- [ ] Props tipadas correctamente
- [ ] Estados de carga manejados
- [ ] Errores mostrados apropiadamente
- [ ] Responsive design implementado
- [ ] Accesibilidad básica (labels, alt texts)

#### 15.3 Funcionalidad
- [ ] CRUD completo funcionando
- [ ] Filtros y búsqueda operativos
- [ ] Paginación trabajando correctamente
- [ ] Validaciones frontend y backend
- [ ] Mensajes de éxito/error
- [ ] Breadcrumbs funcionando
- [ ] Navegación en sidebar

### 16. Troubleshooting Común

#### 16.1 Errores de Namespace
- Verificar que todos los namespaces coincidan con la estructura de directorios
- Asegurar que los imports estén correctos
- Verificar que el autoloader esté actualizado

#### 16.2 Errores de Rutas
- Verificar que las rutas estén incluidas en web.php
- Comprobar que los nombres de rutas sean únicos
- Verificar que los parámetros de ruta coincidan

#### 16.3 Errores de Base de Datos
- Verificar que la migración se haya ejecutado
- Comprobar que los nombres de tabla coincidan
- Verificar que los índices estén creados

Este prompt garantiza la creación de módulos completamente funcionales y consistentes con la arquitectura establecida.
