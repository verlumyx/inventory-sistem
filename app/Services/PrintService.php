<?php

namespace App\Services;

use App\Models\Company;
use App\Inventory\Invoice\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Exception;

class PrintService
{
    /**
     * Ancho de papel termico de 58mm (aproximadamente 32 caracteres)
     */
    const PAPER_WIDTH = 32;

    /**
     * Comandos ESC/POS basicos
     */
    const ESC = "\x1B";
    const GS = "\x1D";
    const INIT = "\x1B\x40";           // Inicializar impresora
    const BOLD_ON = "\x1B\x45\x01";   // Texto en negrita
    const BOLD_OFF = "\x1B\x45\x00";  // Quitar negrita
    const CENTER = "\x1B\x61\x01";    // Centrar texto
    const LEFT = "\x1B\x61\x00";      // Alinear a la izquierda
    const RIGHT = "\x1B\x61\x02";     // Alinear a la derecha
    const CUT = "\x1D\x56\x00";       // Cortar papel
    const FEED = "\x0A";               // Salto de línea

    /**
     * Configuracion de impresion
     */
    private array $config;

    public function __construct(?array $customConfig = null)
    {
        if ($customConfig) {
            // Usar configuración personalizada (desde base de datos)
            $this->config = $customConfig;
        } else {
            // Intentar cargar desde base de datos primero
            $this->config = $this->loadConfigFromDatabase();

            // Si no hay configuración en BD, usar archivo config
            if (!$this->config['enabled']) {
                $this->config = array_merge($this->config, config('printing', []));
            }
        }
    }

    /**
     * Cargar configuracion desde base de datos
     */
    private function loadConfigFromDatabase(): array
    {
        try {
            $settings = \App\Models\PrinterSettings::getActive();

            if ($settings) {
                return $settings->toPrintConfig();
            }
        } catch (\Exception $e) {
            // Si hay error (ej: tabla no existe), usar configuración por defecto
            \Log::debug('No se pudo cargar configuración de impresora desde BD: ' . $e->getMessage());
        }

        // Configuración por defecto si no hay en BD (sin crear registro)
        return [
            'enabled' => false, // Por defecto deshabilitado si no hay configuración
            'port' => env('PRINTING_PORT', '/dev/usb/lp0'),
            'type' => env('PRINTING_TYPE', 'usb'),
            'timeout' => env('PRINTING_TIMEOUT', 5),
        ];
    }

    /**
     * Imprimir factura
     */
    public function printInvoice(Invoice $invoice): bool
    {
        try {
            Log::info('Iniciando impresión de factura', ['invoice_id' => $invoice->id]);

            // Verificar que la impresión esté habilitada
            if (!$this->config['enabled']) {
                throw new Exception('La impresión no está habilitada en la configuración');
            }

            // Verificar que la factura esté pagada
            if (!$invoice->is_paid) {
                throw new Exception('Solo se pueden imprimir facturas pagadas');
            }

            // Obtener datos de la empresa
            $company = Company::getCompany();
            if (!$company) {
                throw new Exception('No se han configurado los datos de la empresa');
            }

            // Generar contenido del ticket
            $ticketContent = $this->formatInvoiceFor58mm($invoice, $company);

            // Generar comandos ESC/POS
            $escposCommands = $this->generateEscPosCommands($ticketContent);

            // Enviar a impresora
            $result = $this->sendToPrinter($escposCommands);

            Log::info('Factura impresa exitosamente', [
                'invoice_id' => $invoice->id,
                'invoice_code' => $invoice->code
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('Error al imprimir factura', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Formatear factura para papel de 58mm
     */
    private function formatInvoiceFor58mm(Invoice $invoice, Company $company): array
    {
        $lines = [];

        // Cabecera de la empresa
        $lines[] = ['text' => $this->centerText($company->name_company), 'style' => 'bold'];
        $lines[] = ['text' => $this->centerText('RIF: ' . $company->dni), 'style' => 'normal'];
        $lines[] = ['text' => $this->wrapText($company->address), 'style' => 'normal'];
        $lines[] = ['text' => $this->centerText($company->phone), 'style' => 'normal'];
        $lines[] = ['text' => $this->repeatChar('=', self::PAPER_WIDTH), 'style' => 'normal'];

        // Informacion de la factura
        $lines[] = ['text' => $this->centerText('FACTURA'), 'style' => 'bold'];
        $lines[] = ['text' => 'No: ' . $invoice->code, 'style' => 'normal'];
        $lines[] = ['text' => 'Fecha: ' . $invoice->created_at->format('d/m/Y H:i'), 'style' => 'normal'];
        $lines[] = ['text' => 'Almacen: ' . $this->normalizeText($invoice->warehouse->name), 'style' => 'normal'];
        $lines[] = ['text' => $this->repeatChar('-', self::PAPER_WIDTH), 'style' => 'normal'];

        // Items
        $items = $invoice->items ?? $invoice->invoiceItems ?? collect();
        foreach ($items as $item) {
            // Nombre del item
            $lines[] = ['text' => $this->wrapText($item->item->name), 'style' => 'normal'];

            // Cantidad x Precio = Subtotal
            $qty = number_format($item->amount, 2);

            if ($invoice->should_show_rate) {
                // Cuando hay tasa, mostrar precios en bolívares
                $priceInBs = $item->price * $invoice->rate;
                $subtotalInBs = $item->subtotal * $invoice->rate;

                $price = 'Bs' . number_format($priceInBs, 2);
                $subtotal = 'Bs' . number_format($subtotalInBs, 2);
            } else {
                // Sin tasa, mostrar precios en dólares
                $price = '$' . number_format($item->price, 2);
                $subtotal = '$' . number_format($item->subtotal, 2);
            }

            $itemLine = $qty . ' x ' . $price;
            $spaces = self::PAPER_WIDTH - strlen($itemLine) - strlen($subtotal);
            $itemLine .= str_repeat(' ', max(1, $spaces)) . $subtotal;

            $lines[] = ['text' => $itemLine, 'style' => 'normal'];
        }

        $lines[] = ['text' => $this->repeatChar('-', self::PAPER_WIDTH), 'style' => 'normal'];

        // Total - formato diferente si hay tasa de cambio
        if ($invoice->should_show_rate) {
            // Cuando hay tasa, mostrar primero el total en bolívares
            $totalBsText = 'TOTAL Bs: ' . number_format($invoice->total_amount_bs, 2);
            $lines[] = ['text' => $this->rightAlign($totalBsText), 'style' => 'bold'];

            // Luego el total de referencia en dólares (en minúsculas)
            $totalRefText = 'total ref: $' . number_format($invoice->total_amount, 2);
            $lines[] = ['text' => $this->rightAlign($totalRefText), 'style' => 'normal'];

            // Finalmente la tasa
            $lines[] = ['text' => 'Tasa: ' . number_format($invoice->rate, 4), 'style' => 'normal'];
        } else {
            // Cuando no hay tasa, mostrar solo el total en dólares
            $totalText = 'TOTAL: $' . number_format($invoice->total_amount, 2);
            $lines[] = ['text' => $this->rightAlign($totalText), 'style' => 'bold'];
        }

        $lines[] = ['text' => $this->repeatChar('=', self::PAPER_WIDTH), 'style' => 'normal'];

        // Pie de página
        $lines[] = ['text' => $this->centerText('Gracias por su compra!'), 'style' => 'normal'];
        $lines[] = ['text' => '', 'style' => 'normal']; // Línea en blanco
        $lines[] = ['text' => '', 'style' => 'normal']; // Línea en blanco

        return $lines;
    }

    /**
     * Generar comandos ESC/POS
     */
    private function generateEscPosCommands(array $lines): string
    {
        $commands = self::INIT; // Inicializar impresora

        foreach ($lines as $line) {
            // Aplicar estilo
            if ($line['style'] === 'bold') {
                $commands .= self::BOLD_ON;
            } else {
                $commands .= self::BOLD_OFF;
            }

            // Agregar texto y salto de línea
            $commands .= $line['text'] . self::FEED;
        }

        // Cortar papel
        $commands .= self::CUT;

        return $commands;
    }

    /**
     * Enviar datos a la impresora
     */
    private function sendToPrinter(string $data): bool
    {
        try {
            switch ($this->config['type']) {
                case 'usb':
                case 'serial':
                    return $this->sendToSerialPort($data);

                case 'network':
                    return $this->sendToNetworkPrinter($data);

                case 'cups':
                case 'macos':
                    return $this->sendToCupsPrinter($data);

                default:
                    throw new Exception('Tipo de impresora no soportado: ' . $this->config['type']);
            }
        } catch (Exception $e) {
            Log::error('Error enviando datos a impresora', [
                'error' => $e->getMessage(),
                'config' => $this->config
            ]);
            throw $e;
        }
    }

    /**
     * Enviar a puerto serial/USB
     */
    private function sendToSerialPort(string $data): bool
    {
        $port = $this->config['port'];

        // Detectar si estamos en Windows
        if (PHP_OS_FAMILY === 'Windows') {
            return $this->sendToWindowsPort($port, $data);
        }

        // Lógica original para Linux/macOS
        if (!file_exists($port)) {
            throw new Exception("Puerto de impresora no encontrado: {$port}");
        }

        $handle = fopen($port, 'w');
        if (!$handle) {
            throw new Exception("No se pudo abrir el puerto: {$port}");
        }

        $result = fwrite($handle, $data);
        fclose($handle);

        return $result !== false;
    }

    /**
     * Enviar datos a puerto en Windows
     */
    private function sendToWindowsPort(string $port, string $data): bool
    {
        // Para puertos USB en Windows, usar el comando copy
        if (strpos($port, 'USB') !== false) {
            return $this->sendToWindowsUSB($port, $data);
        }

        // Para puertos COM en Windows
        if (strpos($port, 'COM') !== false) {
            return $this->sendToWindowsCOM($port, $data);
        }

        // Si no es USB ni COM, intentar como nombre de impresora
        return $this->sendToWindowsPrinter($port, $data);
    }

    /**
     * Enviar a puerto USB en Windows
     */
    private function sendToWindowsUSB(string $port, string $data): bool
    {
        try {
            // Crear archivo temporal
            $tempFile = tempnam(sys_get_temp_dir(), 'print_');
            file_put_contents($tempFile, $data);

            // Usar comando copy para enviar a puerto USB
            $command = sprintf('copy /B "%s" "%s"', $tempFile, $port);

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            // Limpiar archivo temporal
            unlink($tempFile);

            if ($returnCode !== 0) {
                throw new Exception('Error enviando a puerto USB: ' . implode(' ', $output));
            }

            Log::info('Datos enviados a puerto USB Windows', [
                'port' => $port,
                'command' => $command
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error enviando a puerto USB Windows', [
                'port' => $port,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Enviar a puerto COM en Windows
     */
    private function sendToWindowsCOM(string $port, string $data): bool
    {
        try {
            // Crear archivo temporal
            $tempFile = tempnam(sys_get_temp_dir(), 'print_');
            file_put_contents($tempFile, $data);

            // Usar comando copy para enviar a puerto COM
            $command = sprintf('copy /B "%s" "%s"', $tempFile, $port);

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            // Limpiar archivo temporal
            unlink($tempFile);

            if ($returnCode !== 0) {
                throw new Exception('Error enviando a puerto COM: ' . implode(' ', $output));
            }

            Log::info('Datos enviados a puerto COM Windows', [
                'port' => $port,
                'command' => $command
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error enviando a puerto COM Windows', [
                'port' => $port,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Enviar a impresora por nombre en Windows
     */
    private function sendToWindowsPrinter(string $printerName, string $data): bool
    {
        try {
            // Crear archivo temporal
            $tempFile = tempnam(sys_get_temp_dir(), 'print_');
            file_put_contents($tempFile, $data);

            // Usar comando print para enviar a impresora por nombre
            $command = sprintf('print /D:"%s" "%s"', $printerName, $tempFile);

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            // Limpiar archivo temporal
            unlink($tempFile);

            if ($returnCode !== 0) {
                throw new Exception('Error enviando a impresora: ' . implode(' ', $output));
            }

            Log::info('Datos enviados a impresora Windows', [
                'printer' => $printerName,
                'command' => $command
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error enviando a impresora Windows', [
                'printer' => $printerName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Enviar a impresora de red
     */
    private function sendToNetworkPrinter(string $data): bool
    {
        // Implementar envío por red si es necesario
        throw new Exception('Impresión por red no implementada aún');
    }

    /**
     * Enviar a impresora usando CUPS (macOS/Linux) o sistema nativo (Windows)
     */
    private function sendToCupsPrinter(string $data): bool
    {
        $printerName = $this->config['printer_name'] ?? $this->config['port'];

        if (empty($printerName)) {
            throw new Exception('Nombre de impresora no configurado');
        }

        // Detectar sistema operativo
        if (PHP_OS_FAMILY === 'Windows') {
            return $this->sendToWindowsPrinter($printerName, $data);
        }

        // Lógica original para macOS/Linux con CUPS
        return $this->sendToCupsLinux($printerName, $data);
    }

    /**
     * Enviar a impresora usando CUPS en Linux/macOS
     */
    private function sendToCupsLinux(string $printerName, string $data): bool
    {
        // Crear archivo temporal con los datos
        $tempFile = tempnam(sys_get_temp_dir(), 'invoice_print_');
        if (!$tempFile) {
            throw new Exception('No se pudo crear archivo temporal');
        }

        try {
            // Escribir datos al archivo temporal
            file_put_contents($tempFile, $data);

            // Usar lp command para enviar a la impresora
            $command = sprintf(
                'lp -d %s -o raw %s 2>&1',
                escapeshellarg($printerName),
                escapeshellarg($tempFile)
            );

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception('Error ejecutando comando lp: ' . implode(' ', $output));
            }

            Log::info('Datos enviados a impresora CUPS Linux/macOS', [
                'printer' => $printerName,
                'command' => $command,
                'output' => $output
            ]);

            return true;

        } finally {
            // Limpiar archivo temporal
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Centrar texto
     */
    private function centerText(string $text): string
    {
        $text = $this->normalizeText($text);
        $length = strlen($text);
        if ($length >= self::PAPER_WIDTH) {
            return substr($text, 0, self::PAPER_WIDTH);
        }

        $padding = (self::PAPER_WIDTH - $length) / 2;
        $leftPadding = floor($padding);
        $rightPadding = self::PAPER_WIDTH - $length - $leftPadding;

        return str_repeat(' ', $leftPadding) . $text . str_repeat(' ', $rightPadding);
    }

    /**
     * Alinear texto a la derecha
     */
    private function rightAlign(string $text): string
    {
        $text = $this->normalizeText($text);
        $length = strlen($text);
        if ($length >= self::PAPER_WIDTH) {
            return substr($text, 0, self::PAPER_WIDTH);
        }

        return str_repeat(' ', self::PAPER_WIDTH - $length) . $text;
    }

    /**
     * Envolver texto largo
     */
    private function wrapText(string $text): string
    {
        $text = $this->normalizeText($text);
        return wordwrap($text, self::PAPER_WIDTH, "\n", true);
    }

    /**
     * Repetir carácter
     */
    private function repeatChar(string $char, int $times): string
    {
        return str_repeat($char, $times);
    }

    /**
     * Normalizar texto para impresión térmica
     * Elimina acentos y caracteres especiales que pueden causar problemas
     * Esta función es pública para que pueda ser usada desde otros lugares
     */
    public function normalizeText(string $text): string
    {
        // Mapa de caracteres con acentos a sin acentos
        $replacements = [
            // Vocales con acentos
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ā' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e', 'ē' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i', 'ī' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'ō' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u', 'ū' => 'u',

            // Mayúsculas
            'Á' => 'A', 'À' => 'A', 'Ä' => 'A', 'Â' => 'A', 'Ā' => 'A', 'Ã' => 'A',
            'É' => 'E', 'È' => 'E', 'Ë' => 'E', 'Ê' => 'E', 'Ē' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Ï' => 'I', 'Î' => 'I', 'Ī' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ö' => 'O', 'Ô' => 'O', 'Ō' => 'O', 'Õ' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Ü' => 'U', 'Û' => 'U', 'Ū' => 'U',

            // Caracteres especiales del español
            'ñ' => 'n', 'Ñ' => 'N',
            'ç' => 'c', 'Ç' => 'C',

            // Signos de puntuación problemáticos
            '¡' => '', '¿' => '',
            '–' => '-', '—' => '-',
        ];

        // Aplicar reemplazos
        $normalizedText = strtr($text, $replacements);

        return $normalizedText;
    }

    /**
     * Verificar si la impresión está disponible
     */
    public function isAvailable(): bool
    {
        if (!$this->config['enabled']) {
            return false;
        }

        switch ($this->config['type']) {
            case 'usb':
            case 'serial':
                return file_exists($this->config['port']);

            case 'cups':
            case 'macos':
                return $this->isCupsPrinterAvailable();

            case 'network':
                // Para red, asumimos que está disponible si está habilitado
                return true;

            default:
                return false;
        }
    }

    /**
     * Verificar si la impresora CUPS está disponible
     */
    private function isCupsPrinterAvailable(): bool
    {
        $printerName = $this->config['printer_name'] ?? $this->config['port'];

        if (empty($printerName)) {
            return false;
        }

        // Verificar si la impresora existe en CUPS
        $command = sprintf('lpstat -p %s 2>/dev/null', escapeshellarg($printerName));
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        return $returnCode === 0;
    }

    /**
     * Obtener estado de la impresora
     */
    public function getStatus(): array
    {
        return [
            'enabled' => $this->config['enabled'],
            'port' => $this->config['port'],
            'type' => $this->config['type'],
            'available' => $this->isAvailable(),
        ];
    }

    /**
     * Enviar datos de prueba a la impresora
     */
    public function sendTestData(string $data): bool
    {
        try {
            Log::info('Enviando datos de prueba a impresora', [
                'data_length' => strlen($data),
                'config' => $this->config
            ]);

            return $this->sendToPrinter($data);

        } catch (Exception $e) {
            Log::error('Error enviando datos de prueba', [
                'error' => $e->getMessage(),
                'config' => $this->config
            ]);
            throw $e;
        }
    }

    /**
     * Imprimir página de prueba
     */
    public function printTest(): bool
    {
        if (!$this->config['enabled']) {
            throw new Exception('La impresión no está habilitada');
        }

        $testData = $this->generateTestPage();
        return $this->sendToPrinter($testData);
    }

    /**
     * Generar página de prueba
     */
    private function generateTestPage(): string
    {
        $data = self::INIT; // Reset printer
        $data .= self::CENTER; // Center align
        $data .= self::BOLD_ON;
        $data .= "=== PAGINA DE PRUEBA ===\n\n";
        $data .= self::BOLD_OFF;
        $data .= self::LEFT; // Left align
        $data .= "Sistema de Inventario\n";
        $data .= "Fecha: " . now()->format('d/m/Y H:i:s') . "\n";
        $data .= "Impresora: " . ($this->config['port'] ?? 'N/A') . "\n";
        $data .= "Tipo: " . ($this->config['type'] ?? 'N/A') . "\n\n";
        $data .= "Si puede leer este texto,\n";
        $data .= "la impresora esta funcionando\n";
        $data .= "correctamente.\n\n";
        $data .= str_repeat("-", 32) . "\n\n";
        $data .= self::CUT; // Cut paper

        return $data;
    }
}
