<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración de Impresión Térmica
    |--------------------------------------------------------------------------
    |
    | Esta configuración define los parámetros para la impresión térmica
    | de facturas en papel de 58mm. Puedes configurar diferentes tipos
    | de conexión y parámetros específicos de tu impresora.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Impresión Habilitada
    |--------------------------------------------------------------------------
    |
    | Determina si la funcionalidad de impresión está habilitada.
    | Útil para deshabilitar la impresión en entornos de desarrollo.
    |
    */

    'enabled' => env('PRINTING_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Tipo de Conexión
    |--------------------------------------------------------------------------
    |
    | Tipo de conexión con la impresora térmica:
    | - 'usb': Conexión USB directa (Linux)
    | - 'serial': Conexión por puerto serial
    | - 'network': Conexión por red (IP)
    | - 'cups': Impresora configurada en CUPS (macOS/Linux)
    | - 'macos': Alias para 'cups' en macOS
    |
    */

    'type' => env('PRINTING_TYPE', 'cups'),

    /*
    |--------------------------------------------------------------------------
    | Puerto de Conexión / Nombre de Impresora
    |--------------------------------------------------------------------------
    |
    | Puerto o dirección para conectar con la impresora:
    | - USB/Serial: /dev/usb/lp0, /dev/ttyUSB0, COM1, etc.
    | - Red: IP:Puerto (ej: 192.168.1.100:9100)
    | - CUPS/macOS: Nombre de la impresora (ej: TECH_CLA58)
    |
    */

    'port' => env('PRINTING_PORT', 'TECH_CLA58'),

    /*
    |--------------------------------------------------------------------------
    | Nombre de Impresora (CUPS)
    |--------------------------------------------------------------------------
    |
    | Nombre específico de la impresora en CUPS. Si no se especifica,
    | se usará el valor de 'port'.
    |
    */

    'printer_name' => env('PRINTING_PRINTER_NAME', null),

    /*
    |--------------------------------------------------------------------------
    | Timeout de Conexión
    |--------------------------------------------------------------------------
    |
    | Tiempo límite en segundos para establecer conexión con la impresora.
    |
    */

    'timeout' => env('PRINTING_TIMEOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Configuración del Papel
    |--------------------------------------------------------------------------
    |
    | Configuración específica para papel térmico de 58mm.
    |
    */

    'paper' => [
        'width' => 32,          // Caracteres por línea (58mm ≈ 32 chars)
        'margin' => 0,          // Margen en caracteres
        'line_spacing' => 1,    // Espaciado entre líneas
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Texto
    |--------------------------------------------------------------------------
    |
    | Configuración para el formato del texto en el ticket.
    |
    */

    'text' => [
        'encoding' => 'UTF-8',
        'font_size' => 'normal',    // normal, small, large
        'line_height' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Comandos ESC/POS Personalizados
    |--------------------------------------------------------------------------
    |
    | Comandos ESC/POS específicos para tu modelo de impresora.
    | Los valores por defecto funcionan con la mayoría de impresoras.
    |
    */

    'commands' => [
        'init' => "\x1B\x40",           // Inicializar impresora
        'bold_on' => "\x1B\x45\x01",   // Activar negrita
        'bold_off' => "\x1B\x45\x00",  // Desactivar negrita
        'center' => "\x1B\x61\x01",    // Centrar texto
        'left' => "\x1B\x61\x00",      // Alinear izquierda
        'right' => "\x1B\x61\x02",     // Alinear derecha
        'cut' => "\x1D\x56\x00",       // Cortar papel
        'feed' => "\x0A",               // Salto de línea
        'double_height' => "\x1B\x21\x10", // Doble altura
        'normal_height' => "\x1B\x21\x00", // Altura normal
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Red
    |--------------------------------------------------------------------------
    |
    | Configuración específica para impresoras de red.
    |
    */

    'network' => [
        'host' => env('PRINTING_HOST', '192.168.1.100'),
        'port' => env('PRINTING_NETWORK_PORT', 9100),
        'timeout' => env('PRINTING_NETWORK_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración Serial
    |--------------------------------------------------------------------------
    |
    | Configuración específica para conexiones seriales.
    |
    */

    'serial' => [
        'baud_rate' => env('PRINTING_BAUD_RATE', 9600),
        'data_bits' => env('PRINTING_DATA_BITS', 8),
        'stop_bits' => env('PRINTING_STOP_BITS', 1),
        'parity' => env('PRINTING_PARITY', 'none'), // none, odd, even
        'flow_control' => env('PRINTING_FLOW_CONTROL', 'none'), // none, rts/cts, xon/xoff
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Logging
    |--------------------------------------------------------------------------
    |
    | Configuración para el registro de eventos de impresión.
    |
    */

    'logging' => [
        'enabled' => env('PRINTING_LOG_ENABLED', true),
        'level' => env('PRINTING_LOG_LEVEL', 'info'), // debug, info, warning, error
        'channel' => env('PRINTING_LOG_CHANNEL', 'single'), // single, daily, stack
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Reintentos
    |--------------------------------------------------------------------------
    |
    | Configuración para reintentos automáticos en caso de fallo.
    |
    */

    'retry' => [
        'enabled' => env('PRINTING_RETRY_ENABLED', true),
        'attempts' => env('PRINTING_RETRY_ATTEMPTS', 3),
        'delay' => env('PRINTING_RETRY_DELAY', 1), // segundos entre reintentos
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Validación
    |--------------------------------------------------------------------------
    |
    | Validaciones antes de imprimir.
    |
    */

    'validation' => [
        'check_port' => env('PRINTING_CHECK_PORT', true),
        'check_company_data' => env('PRINTING_CHECK_COMPANY', true),
        'only_paid_invoices' => env('PRINTING_ONLY_PAID', true),
    ],

];
