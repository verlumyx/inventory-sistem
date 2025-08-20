# 🖨️ Guía de Impresión Térmica

Esta guía explica cómo configurar y usar la funcionalidad de impresión térmica de 58mm para facturas en el sistema de inventario.

## 📋 Requisitos

- Impresora térmica de 58mm compatible con comandos ESC/POS
- Conexión USB, Serial o de Red
- Laravel 10+ con PHP 8.1+
- Permisos de escritura en el puerto de la impresora (Linux/macOS)

## ⚙️ Configuración Inicial

### 1. Configurar Datos de la Empresa

Antes de imprimir, debes configurar los datos de tu empresa:

1. Ve a **Configuración → Configuración de Empresa**
2. Completa todos los campos:
   - Nombre de la empresa
   - RIF/DNI
   - Dirección completa
   - Teléfono de contacto

### 2. Configurar Variables de Entorno

Agrega estas variables a tu archivo `.env`:

#### Configuración Básica
```bash
# Habilitar impresión
PRINTING_ENABLED=true

# Tipo de conexión (usb, serial, network)
PRINTING_TYPE=usb

# Puerto de la impresora
PRINTING_PORT=/dev/usb/lp0  # Linux
# PRINTING_PORT=COM1        # Windows

# Timeout de conexión
PRINTING_TIMEOUT=5
```

#### Para Impresoras USB (Recomendado)
```bash
PRINTING_ENABLED=true
PRINTING_TYPE=usb
PRINTING_PORT=/dev/usb/lp0
PRINTING_TIMEOUT=5
```

#### Para Impresoras Serial
```bash
PRINTING_ENABLED=true
PRINTING_TYPE=serial
PRINTING_PORT=/dev/ttyUSB0
PRINTING_BAUD_RATE=9600
PRINTING_DATA_BITS=8
PRINTING_STOP_BITS=1
PRINTING_PARITY=none
```

#### Para Impresoras de Red
```bash
PRINTING_ENABLED=true
PRINTING_TYPE=network
PRINTING_HOST=192.168.1.100
PRINTING_NETWORK_PORT=9100
PRINTING_NETWORK_TIMEOUT=10
```

### 3. Configurar Permisos (Linux/macOS)

```bash
# Verificar que la impresora esté conectada
lsusb

# Verificar el puerto
ls -la /dev/usb/lp*

# Agregar usuario al grupo de impresoras
sudo usermod -a -G lp $USER

# Dar permisos al puerto (temporal)
sudo chmod 666 /dev/usb/lp0

# Probar conexión
echo "Test" > /dev/usb/lp0
```

## 🖨️ Cómo Imprimir Facturas

### Desde la Interfaz Web

1. Ve a **Facturas** y selecciona una factura
2. Asegúrate de que la factura esté **marcada como pagada**
3. Haz clic en el botón **"Imprimir"** (icono de impresora azul)
4. Espera la confirmación de impresión exitosa

### Formato del Ticket

El ticket impreso incluye:

```
================================
        NOMBRE DE LA EMPRESA
      RIF: J-12345678-9
    Dirección de la empresa
        +58 412-123-4567
================================
            FACTURA
No: FAC-001
Fecha: 20/08/2025 14:30
Almacen: Almacén Principal
--------------------------------
Producto 1
2.00 x $10.00              $20.00
Producto 2
1.00 x $15.50              $15.50
--------------------------------
                    TOTAL: $35.50
                 TOTAL Bs: 1,278.00
                 Tasa: 36.0000
================================
      ¡Gracias por su compra!


```

## 🔧 Solución de Problemas

### Error: "La impresora no está disponible"

**Causas posibles:**
- `PRINTING_ENABLED=false` en el `.env`
- Puerto incorrecto o no existe
- Falta de permisos en el puerto
- Impresora desconectada

**Soluciones:**
1. Verificar que `PRINTING_ENABLED=true`
2. Comprobar que el puerto existe: `ls /dev/usb/lp*`
3. Verificar permisos: `ls -la /dev/usb/lp0`
4. Probar conexión: `echo "test" > /dev/usb/lp0`

### Error: "Solo se pueden imprimir facturas pagadas"

**Causa:** La factura no está marcada como pagada.

**Solución:** Marca la factura como pagada antes de imprimir.

### Error: "No se han configurado los datos de la empresa"

**Causa:** Faltan los datos de la empresa.

**Solución:** Ve a Configuración → Configuración de Empresa y completa todos los campos.

### La impresión no sale o sale cortada

**Causas posibles:**
- Papel mal colocado
- Impresora sin papel
- Configuración incorrecta del ancho

**Soluciones:**
1. Verificar que hay papel térmico de 58mm
2. Revisar que el papel esté bien colocado
3. Verificar configuración en `config/printing.php`

## 🛠️ Configuración Avanzada

### Personalizar Comandos ESC/POS

Edita `config/printing.php` para personalizar los comandos:

```php
'commands' => [
    'init' => "\x1B\x40",           // Inicializar
    'bold_on' => "\x1B\x45\x01",   // Negrita ON
    'bold_off' => "\x1B\x45\x00",  // Negrita OFF
    'center' => "\x1B\x61\x01",    // Centrar
    'cut' => "\x1D\x56\x00",       // Cortar papel
],
```

### Configurar Logging

```bash
PRINTING_LOG_ENABLED=true
PRINTING_LOG_LEVEL=info
PRINTING_LOG_CHANNEL=daily
```

### Configurar Reintentos

```bash
PRINTING_RETRY_ENABLED=true
PRINTING_RETRY_ATTEMPTS=3
PRINTING_RETRY_DELAY=1
```

## 📱 Impresoras Recomendadas

### USB
- **Epson TM-T20III**: Confiable, drivers universales
- **Star TSP143III**: Rápida, buena calidad
- **Bixolon SRP-275III**: Económica, compatible

### Bluetooth/WiFi
- **Epson TM-m30**: WiFi, fácil configuración
- **Star TSP143IIIU**: USB + Ethernet
- **Bixolon SRP-350plusIII**: WiFi + USB

## 🔍 Comandos Útiles

### Verificar Estado
```bash
# Ver puertos disponibles
ls /dev/tty* | grep USB

# Ver impresoras USB
lsusb | grep -i print

# Verificar permisos
ls -la /dev/usb/lp*

# Probar impresión
echo -e "\x1B\x40Hello World\x1D\x56\x00" > /dev/usb/lp0
```

### Logs de Impresión
```bash
# Ver logs de Laravel
tail -f storage/logs/laravel.log | grep -i print

# Ver logs del sistema
sudo dmesg | grep -i usb
```

## 📞 Soporte

Si tienes problemas con la impresión:

1. Verifica la configuración paso a paso
2. Revisa los logs en `storage/logs/laravel.log`
3. Prueba con comandos manuales
4. Consulta la documentación de tu impresora

## 🍎 Configuración Específica para macOS

### **Configuración Recomendada para macOS**

Si estás en macOS (como tu caso), usa esta configuración en tu `.env`:

```bash
# Configuración para macOS
PRINTING_ENABLED=true
PRINTING_TYPE=cups
PRINTING_PORT=TECH_CLA58  # Nombre de tu impresora
PRINTING_TIMEOUT=5
PRINTING_RETRY_ENABLED=true
PRINTING_RETRY_ATTEMPTS=3
```

### **Encontrar el Nombre de tu Impresora en macOS**

```bash
# Listar todas las impresoras configuradas
lpstat -p

# Ver estado de una impresora específica
lpstat -p TECH_CLA58

# Probar impresión básica
echo "Prueba" | lp -d TECH_CLA58 -o raw
```

### **Configurar Impresora en macOS**

1. **Conecta la impresora** por USB
2. **Ve a Preferencias del Sistema → Impresoras y Escáneres**
3. **Haz clic en "+"** para agregar impresora
4. **Selecciona tu impresora** (TECH CLA58)
5. **Configura como "Generic PostScript Printer"** si es necesario
6. **Verifica que aparezca como "Idle"** en el estado

### **Solución de Problemas en macOS**

#### Error: "Impresora no disponible"
```bash
# Verificar que la impresora existe
lpstat -p TECH_CLA58

# Si no aparece, agregarla manualmente
sudo lpadmin -p TECH_CLA58 -E -v usb://TECH/CLA58 -m raw
```

#### Error: "Permission denied"
```bash
# Agregar usuario al grupo de impresión
sudo dseditgroup -o edit -a $USER -t user lpadmin
```

#### Probar conexión directa
```bash
# Enviar datos directamente a la impresora
echo -e "\x1B\x40Prueba de impresión\x0A\x0A\x0A\x1D\x56\x00" | lp -d TECH_CLA58 -o raw
```

---

**Nota:** Esta funcionalidad está optimizada para papel térmico de 58mm. Para otros tamaños, ajusta la configuración en `config/printing.php`.
