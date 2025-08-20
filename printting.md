¡Por supuesto! Te explico detalladamente todas las variables de entorno que agregué para la configuración de impresión térmica:

## **📋 VARIABLES DE CONFIGURACIÓN DE IMPRESIÓN TÉRMICA**

### **🔧 Configuración Básica**

```bash
PRINTING_ENABLED=false
```
- **Propósito**: Habilita/deshabilita toda la funcionalidad de impresión
- **Valores**: `true` o `false`
- **Recomendación**: `false` en desarrollo, `true` en producción

```bash
PRINTING_TYPE=usb
```
- **Propósito**: Define el tipo de conexión con la impresora
- **Valores**: `usb`, `serial`, `network`
- **Uso**: Determina qué método usar para enviar datos a la impresora

```bash
PRINTING_PORT=/dev/usb/lp0
```
- **Propósito**: Puerto o dirección de la impresora
- **Ejemplos**:
    - Linux USB: `/dev/usb/lp0`, `/dev/usb/lp1`
    - Linux Serial: `/dev/ttyUSB0`, `/dev/ttyS0`
    - Windows: `COM1`, `COM2`, `LPT1`
- **Nota**: Debe existir y tener permisos de escritura

```bash
PRINTING_TIMEOUT=5
```
- **Propósito**: Tiempo límite en segundos para conectar con la impresora
- **Rango**: 1-30 segundos
- **Recomendación**: 5 segundos es suficiente para la mayoría de casos

---

### **🌐 Configuración de Red (para impresoras IP)**

```bash
PRINTING_HOST=192.168.1.100
```
- **Propósito**: Dirección IP de la impresora de red
- **Formato**: Dirección IP válida
- **Ejemplo**: `192.168.1.100`, `10.0.0.50`

```bash
PRINTING_NETWORK_PORT=9100
```
- **Propósito**: Puerto TCP de la impresora de red
- **Valor común**: `9100` (puerto estándar para impresoras)
- **Otros puertos**: `515` (LPD), `631` (IPP)

```bash
PRINTING_NETWORK_TIMEOUT=10
```
- **Propósito**: Timeout específico para conexiones de red
- **Rango**: 5-30 segundos
- **Nota**: Las conexiones de red pueden ser más lentas que USB/Serial

---

### **📡 Configuración Serial (para puertos COM/ttyUSB)**

```bash
PRINTING_BAUD_RATE=9600
```
- **Propósito**: Velocidad de transmisión de datos
- **Valores comunes**: `9600`, `19200`, `38400`, `115200`
- **Nota**: Debe coincidir con la configuración de la impresora

```bash
PRINTING_DATA_BITS=8
```
- **Propósito**: Número de bits de datos por carácter
- **Valores**: `7` o `8`
- **Estándar**: `8` bits es lo más común

```bash
PRINTING_STOP_BITS=1
```
- **Propósito**: Bits de parada para sincronización
- **Valores**: `1` o `2`
- **Estándar**: `1` bit es lo más común

```bash
PRINTING_PARITY=none
```
- **Propósito**: Control de paridad para detección de errores
- **Valores**: `none`, `odd`, `even`
- **Recomendación**: `none` para la mayoría de impresoras térmicas

```bash
PRINTING_FLOW_CONTROL=none
```
- **Propósito**: Control de flujo de datos
- **Valores**: `none`, `rts/cts`, `xon/xoff`
- **Recomendación**: `none` para impresoras simples

---

### **📊 Configuración de Logging**

```bash
PRINTING_LOG_ENABLED=true
```
- **Propósito**: Habilita el registro de eventos de impresión
- **Valores**: `true` o `false`
- **Beneficio**: Útil para debugging y auditoría

```bash
PRINTING_LOG_LEVEL=info
```
- **Propósito**: Nivel de detalle en los logs
- **Valores**: `debug`, `info`, `warning`, `error`
- **Recomendación**: `info` en producción, `debug` para troubleshooting

```bash
PRINTING_LOG_CHANNEL=single
```
- **Propósito**: Canal de logging de Laravel a usar
- **Valores**: `single`, `daily`, `stack`
- **Nota**: Usa la configuración de `config/logging.php`

---

### **🔄 Configuración de Reintentos**

```bash
PRINTING_RETRY_ENABLED=true
```
- **Propósito**: Habilita reintentos automáticos en caso de fallo
- **Valores**: `true` o `false`
- **Beneficio**: Mejora la confiabilidad de la impresión

```bash
PRINTING_RETRY_ATTEMPTS=3
```
- **Propósito**: Número máximo de reintentos
- **Rango**: 1-10
- **Recomendación**: 3 intentos es un buen balance

```bash
PRINTING_RETRY_DELAY=1
```
- **Propósito**: Segundos de espera entre reintentos
- **Rango**: 1-10 segundos
- **Nota**: Evita saturar la impresora con intentos rápidos

---

### **✅ Configuración de Validaciones**

```bash
PRINTING_CHECK_PORT=true
```
- **Propósito**: Verifica que el puerto existe antes de imprimir
- **Valores**: `true` o `false`
- **Beneficio**: Evita errores de "puerto no encontrado"

```bash
PRINTING_CHECK_COMPANY=true
```
- **Propósito**: Verifica que los datos de empresa estén configurados
- **Valores**: `true` o `false`
- **Nota**: Necesario para generar la cabecera del ticket

```bash
PRINTING_ONLY_PAID=true
```
- **Propósito**: Solo permite imprimir facturas pagadas
- **Valores**: `true` o `false`
- **Seguridad**: Evita imprimir facturas pendientes por error

---

## **🚀 Configuraciones Recomendadas por Escenario**

### **💻 Desarrollo Local**
```bash
PRINTING_ENABLED=false
PRINTING_LOG_ENABLED=true
PRINTING_LOG_LEVEL=debug
```

### **🏢 Producción con Impresora USB**
```bash
PRINTING_ENABLED=true
PRINTING_TYPE=usb
PRINTING_PORT=/dev/usb/lp0
PRINTING_TIMEOUT=5
PRINTING_RETRY_ENABLED=true
PRINTING_RETRY_ATTEMPTS=3
```

### **🌐 Producción con Impresora de Red**
```bash
PRINTING_ENABLED=true
PRINTING_TYPE=network
PRINTING_HOST=192.168.1.100
PRINTING_NETWORK_PORT=9100
PRINTING_NETWORK_TIMEOUT=10
```

### **📡 Producción con Impresora Serial**
```bash
PRINTING_ENABLED=true
PRINTING_TYPE=serial
PRINTING_PORT=/dev/ttyUSB0
PRINTING_BAUD_RATE=9600
PRINTING_DATA_BITS=8
PRINTING_STOP_BITS=1
PRINTING_PARITY=none
```

Estas variables te dan control total sobre cómo funciona la impresión térmica en tu aplicación. ¿Hay alguna configuración específica que te gustaría que explique más a fondo?
