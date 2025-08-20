<?php

namespace App\Services;

use App\Models\Company;
use App\Inventory\Invoice\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Exception;

class PrintService
{
    /**
     * Ancho de papel térmico de 58mm (aproximadamente 32 caracteres)
     */
    const PAPER_WIDTH = 32;
    
    /**
     * Comandos ESC/POS básicos
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
     * Configuración de impresión
     */
    private array $config;

    public function __construct()
    {
        $this->config = config('printing', [
            'enabled' => env('PRINTING_ENABLED', false),
            'port' => env('PRINTING_PORT', '/dev/usb/lp0'),
            'type' => env('PRINTING_TYPE', 'usb'), // usb, serial, network
            'timeout' => env('PRINTING_TIMEOUT', 5),
        ]);
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

        // Información de la factura
        $lines[] = ['text' => $this->centerText('FACTURA'), 'style' => 'bold'];
        $lines[] = ['text' => 'No: ' . $invoice->code, 'style' => 'normal'];
        $lines[] = ['text' => 'Fecha: ' . $invoice->created_at->format('d/m/Y H:i'), 'style' => 'normal'];
        $lines[] = ['text' => 'Almacen: ' . $invoice->warehouse->name, 'style' => 'normal'];
        $lines[] = ['text' => $this->repeatChar('-', self::PAPER_WIDTH), 'style' => 'normal'];

        // Items
        foreach ($invoice->items as $item) {
            // Nombre del item
            $lines[] = ['text' => $this->wrapText($item->item->name), 'style' => 'normal'];
            
            // Cantidad x Precio = Subtotal
            $qty = number_format($item->amount, 2);
            $price = '$' . number_format($item->price, 2);
            $subtotal = '$' . number_format($item->subtotal, 2);
            
            $itemLine = $qty . ' x ' . $price;
            $spaces = self::PAPER_WIDTH - strlen($itemLine) - strlen($subtotal);
            $itemLine .= str_repeat(' ', max(1, $spaces)) . $subtotal;
            
            $lines[] = ['text' => $itemLine, 'style' => 'normal'];
        }

        $lines[] = ['text' => $this->repeatChar('-', self::PAPER_WIDTH), 'style' => 'normal'];

        // Total
        $totalText = 'TOTAL: $' . number_format($invoice->total_amount, 2);
        $lines[] = ['text' => $this->rightAlign($totalText), 'style' => 'bold'];

        // Total en Bolívares si aplica
        if ($invoice->should_show_rate) {
            $totalBsText = 'TOTAL Bs: ' . number_format($invoice->total_amount_bs, 2);
            $lines[] = ['text' => $this->rightAlign($totalBsText), 'style' => 'bold'];
            $lines[] = ['text' => 'Tasa: ' . number_format($invoice->rate, 4), 'style' => 'normal'];
        }

        $lines[] = ['text' => $this->repeatChar('=', self::PAPER_WIDTH), 'style' => 'normal'];

        // Pie de página
        $lines[] = ['text' => $this->centerText('¡Gracias por su compra!'), 'style' => 'normal'];
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
     * Enviar a impresora de red
     */
    private function sendToNetworkPrinter(string $data): bool
    {
        // Implementar envío por red si es necesario
        throw new Exception('Impresión por red no implementada aún');
    }

    /**
     * Centrar texto
     */
    private function centerText(string $text): string
    {
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
     * Verificar si la impresión está disponible
     */
    public function isAvailable(): bool
    {
        return $this->config['enabled'] && file_exists($this->config['port']);
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
}
