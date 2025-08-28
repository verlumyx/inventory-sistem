# Sistema de Licencias - Sistema de Inventario

## Descripción General

El sistema de licencias implementado proporciona control de acceso basado en códigos de activación con duración de 6 meses. Está diseñado específicamente para aplicaciones de escritorio usando Laravel + NativePHP.

## Características Principales

- ✅ **Verificación automática** de licencias en cada acceso
- ✅ **Generación de códigos** únicos de 10 caracteres
- ✅ **Identificación de máquina** para prevenir uso no autorizado
- ✅ **Notificaciones automáticas** de vencimiento
- ✅ **Sistema de logs** completo para auditoría
- ✅ **Interfaz de usuario** intuitiva para renovación
- ✅ **Comandos de consola** para administración

## Flujo de Funcionamiento

### 1. Verificación de Licencia
- El middleware `LicenseMiddleware` verifica cada request
- Si no hay licencia válida, redirige a `/license/renewal`
- Muestra advertencias cuando quedan menos de 7 días

### 2. Solicitud de Renovación
- Usuario hace clic en "Generar Código de Renovación"
- Sistema crea código único y lo envía por email a administradores
- Se registra la actividad en logs

### 3. Activación
- Administradores proporcionan el código al cliente
- Cliente ingresa el código en el sistema
- Sistema valida y activa la licencia por 6 meses

## Estructura de Base de Datos

### Tabla `licenses`
```sql
- id: Primary key
- license_code: Código único de 10 caracteres
- start_date: Fecha de inicio de la licencia
- end_date: Fecha de expiración (6 meses después)
- status: 'pending', 'active', 'expired', 'revoked'
- machine_id: ID único de la máquina
- user_email: Email del usuario (opcional)
- activated_at: Fecha de activación
- notes: Notas adicionales
```

### Tabla `license_logs`
```sql
- id: Primary key
- license_id: FK a licenses
- action: Tipo de acción realizada
- license_code: Código de licencia involucrado
- machine_id: ID de máquina
- user_email: Email del usuario
- ip_address: Dirección IP
- user_agent: User agent del navegador
- metadata: Información adicional (JSON)
- level: 'info', 'warning', 'error'
- message: Mensaje descriptivo
```

## Comandos de Consola

### Verificar Estado
```bash
php artisan license:status
php artisan license:status --logs=20
```

### Verificar Expiración
```bash
php artisan license:check-expiration
```

### Limpiar Logs Antiguos
```bash
php artisan license:cleanup-logs
php artisan license:cleanup-logs --days=60
```

## Configuración

### Variables de Entorno (.env)
```env
LICENSE_ENABLED=true
LICENSE_ADMIN_EMAIL="admin@sistema.com"
LICENSE_DURATION_MONTHS=6
LICENSE_GRACE_PERIOD_DAYS=3
```

### Configuración (config/license.php)
```php
'administrators' => [
    'admin@sistema.com',
    'admin2@sistema.com',
],
'duration_months' => 6,
'warning_days' => [30, 15, 7, 3, 1],
'grace_period_days' => 3,
```

## Tareas Programadas

El sistema incluye tareas automáticas configuradas en `routes/console.php`:

- **Verificación diaria** a las 9:00 AM
- **Verificación cada 6 horas** para casos críticos
- **Limpieza mensual** de logs antiguos

## Rutas del Sistema

```php
GET  /license/renewal     - Página de renovación
POST /license/generate    - Generar nuevo código
POST /license/activate    - Activar licencia
```

## Seguridad

### Validaciones Implementadas
- Verificación de ID de máquina único
- Códigos de un solo uso
- Logs de todos los intentos de acceso
- Encriptación de códigos en emails

### Prevención de Ataques
- Rate limiting en generación de códigos
- Validación de machine_id para prevenir transferencias
- Logs detallados para detectar actividad sospechosa

## Monitoreo y Logs

### Tipos de Logs
- `generated`: Código generado
- `activated`: Licencia activada
- `expired`: Licencia expirada
- `valid_access`: Acceso autorizado
- `access_denied`: Acceso denegado
- `activation_failed`: Intento de activación fallido

### Consulta de Logs
```php
// Logs recientes
$logs = LicenseLog::getRecentLogs(50);

// Logs por acción
$activations = LicenseLog::getLogsByAction('activated');
```

## Troubleshooting

### Problemas Comunes

1. **"Licencia expirada"**
   - Verificar fecha del sistema
   - Ejecutar `php artisan license:status`
   - Generar nuevo código si es necesario

2. **"Código inválido"**
   - Verificar que el código sea exacto (10 caracteres)
   - Confirmar que no haya sido usado anteriormente
   - Verificar logs con `php artisan license:status --logs=20`

3. **"No válido para esta máquina"**
   - El código fue generado en otra máquina
   - Generar nuevo código desde la máquina actual

### Comandos de Diagnóstico
```bash
# Estado completo del sistema
php artisan license:status --logs=50

# Verificar configuración
php artisan config:show license

# Ver logs de Laravel
tail -f storage/logs/laravel.log
```

## Mantenimiento

### Tareas Regulares
- Monitorear logs de actividad sospechosa
- Limpiar logs antiguos mensualmente
- Verificar que las notificaciones de email funcionen
- Revisar licencias próximas a vencer

### Backup
- Incluir tabla `licenses` en backups regulares
- Los logs pueden ser archivados y eliminados periódicamente

## Extensiones Futuras

### Posibles Mejoras
- Panel de administración web
- Múltiples tipos de licencia (básica, premium)
- Licencias por módulos específicos
- API REST para gestión remota
- Integración con sistemas de pago
