# 🔐 Sistema de Encriptación de Datos Sensibles

Este documento describe cómo funciona el sistema de encriptación implementado para proteger datos sensibles como claves de aplicación de Gmail y emails de administradores.

## 📋 Datos Encriptados

Los siguientes datos están encriptados en el archivo `.env`:

- `MAIL_PASSWORD` - Clave de aplicación de Gmail
- `LICENSE_ADMIN_EMAIL_1` - Email del primer administrador
- `LICENSE_ADMIN_EMAIL_2` - Email del segundo administrador

## 🛠️ Comandos Disponibles

### Comando Principal
```bash
php artisan encrypt:sensitive-data
```

### Opciones del Comando

#### Encriptar Email
```bash
php artisan encrypt:sensitive-data --email="usuario@ejemplo.com"
```

#### Encriptar Clave de Aplicación
```bash
php artisan encrypt:sensitive-data --password="tu-clave-de-aplicacion"
```

#### Desencriptar Valor
```bash
php artisan encrypt:sensitive-data --decrypt="valor-encriptado"
```

#### Verificar si un Valor está Encriptado
```bash
php artisan encrypt:sensitive-data --check="valor-a-verificar"
```

## 🔧 Uso Programático

### Servicio de Encriptación

```php
use App\Services\EncryptionService;

// Encriptar
$encrypted = EncryptionService::encrypt('valor-sensible');

// Desencriptar
$decrypted = EncryptionService::decrypt($encrypted);

// Verificar si está encriptado
$isEncrypted = EncryptionService::isEncrypted($value);

// Encriptar email (con validación)
$encryptedEmail = EncryptionService::encryptEmail('usuario@ejemplo.com');

// Desencriptar email
$email = EncryptionService::decryptEmail($encryptedEmail);
```

### Obtener Valores Desencriptados desde Configuración

```php
// Obtener email desencriptado
$email = EncryptionService::getDecryptedEmail('license.administrators.0');

// Obtener clave desencriptada
$password = EncryptionService::getDecryptedPassword('mail.mailers.smtp.password');
```

## ⚙️ Funcionamiento Automático

### Proveedor de Servicios

El `EncryptionServiceProvider` se ejecuta automáticamente al arrancar la aplicación y:

1. **Desencripta la clave de email** y la configura en `mail.mailers.smtp.password`
2. **Desencripta los emails de administradores** y los configura en `license.administrators`
3. **Maneja errores** sin fallar la aplicación

### Configuración Automática

Los datos se desencriptan automáticamente al arrancar la aplicación, por lo que el resto del código funciona normalmente sin cambios.

## 🔒 Seguridad

### Clave de Encriptación

- Usa la clave `APP_KEY` de Laravel para encriptar/desencriptar
- **NUNCA** compartas o versiones la clave `APP_KEY`
- Si cambias `APP_KEY`, deberás re-encriptar todos los datos

### Validaciones

- **Emails**: Se valida que sean emails válidos antes y después de encriptar
- **Claves**: Se valida longitud mínima para claves de aplicación
- **Errores**: Se registran en logs sin exponer datos sensibles

## 📝 Proceso de Implementación

### 1. Encriptar Datos Existentes
```bash
php artisan encrypt:sensitive-data
# Seleccionar: "Encriptar datos actuales del .env"
```

### 2. Actualizar .env
Reemplazar los valores en `.env` con los valores encriptados generados.

### 3. Limpiar Cache
```bash
php artisan config:clear
```

### 4. Verificar Funcionamiento
```bash
php artisan tinker
>>> config('mail.mailers.smtp.password')
>>> config('license.administrators')
```

## 🚨 Troubleshooting

### Error: "Payload is invalid"
- La clave `APP_KEY` cambió después de encriptar
- Re-encripta los datos con la nueva clave

### Error: "Email inválido"
- El email desencriptado no es válido
- Verifica que el email original fuera correcto

### Los emails no se envían
- Verifica que `MAIL_PASSWORD` se desencripte correctamente
- Revisa los logs de Laravel para errores específicos

## 📊 Verificación del Estado

### Comando de Estado
```bash
php artisan encrypt:sensitive-data
# Seleccionar: "Mostrar configuración actual"
```

### Verificación Manual
```php
use App\Services\EncryptionService;

// Verificar si los datos están encriptados
$mailPassword = env('MAIL_PASSWORD');
echo EncryptionService::isEncrypted($mailPassword) ? 'Encriptado' : 'Sin encriptar';
```

## 🔄 Rotación de Datos

### Para Cambiar la Clave de Gmail
1. Generar nueva clave de aplicación en Gmail
2. Encriptarla: `php artisan encrypt:sensitive-data --password="nueva-clave"`
3. Actualizar `MAIL_PASSWORD` en `.env`
4. Limpiar cache: `php artisan config:clear`

### Para Cambiar Emails de Administradores
1. Encriptar nuevo email: `php artisan encrypt:sensitive-data --email="nuevo@email.com"`
2. Actualizar `LICENSE_ADMIN_EMAIL_X` en `.env`
3. Limpiar cache: `php artisan config:clear`

## ✅ Beneficios

- **Seguridad**: Datos sensibles encriptados en reposo
- **Transparencia**: El código funciona igual que antes
- **Flexibilidad**: Fácil rotación de credenciales
- **Auditoría**: Logs de todas las operaciones de encriptación
- **Validación**: Verificación automática de integridad de datos
