<?php

namespace App\Services;

use App\Services\PrintService;
use App\Inventory\Invoice\Models\Invoice;
use App\Models\Company;

/**
 * Ejemplo de uso del servicio de impresión
 * 
 * Este archivo muestra cómo usar el PrintService para imprimir facturas
 * en impresoras térmicas de 58mm.
 */
class PrintServiceExample
{
    private PrintService $printService;

    public function __construct()
    {
        $this->printService = new PrintService();
    }

    /**
     * Ejemplo básico de impresión de factura
     */
    public function basicPrintExample(int $invoiceId): array
    {
        try {
            // 1. Obtener la factura con sus relaciones
            $invoice = Invoice::with(['warehouse', 'invoiceItems.item'])->findOrFail($invoiceId);
            
            // 2. Verificar que la factura esté pagada
            if (!$invoice->is_paid) {
                return [
                    'success' => false,
                    'message' => 'La factura debe estar pagada para poder imprimirse'
                ];
            }

            // 3. Verificar que existan datos de empresa
            $company = Company::getCompany();
            if (!$company) {
                return [
                    'success' => false,
                    'message' => 'Debe configurar los datos de la empresa antes de imprimir'
                ];
            }

            // 4. Verificar que la impresión esté disponible
            if (!$this->printService->isAvailable()) {
                return [
                    'success' => false,
                    'message' => 'La impresora no está disponible o la impresión está deshabilitada'
                ];
            }

            // 5. Imprimir la factura
            $result = $this->printService->printInvoice($invoice);

            return [
                'success' => $result,
                'message' => $result ? 'Factura impresa exitosamente' : 'Error al imprimir la factura'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar estado de la impresora
     */
    public function checkPrinterStatus(): array
    {
        $status = $this->printService->getStatus();
        
        return [
            'printer_status' => $status,
            'recommendations' => $this->getRecommendations($status)
        ];
    }

    /**
     * Obtener recomendaciones basadas en el estado
     */
    private function getRecommendations(array $status): array
    {
        $recommendations = [];

        if (!$status['enabled']) {
            $recommendations[] = 'Habilite la impresión en el archivo .env: PRINTING_ENABLED=true';
        }

        if (!$status['available']) {
            $recommendations[] = 'Verifique que la impresora esté conectada al puerto: ' . $status['port'];
            $recommendations[] = 'Asegúrese de que el puerto tenga los permisos correctos';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'La impresora está lista para usar';
        }

        return $recommendations;
    }

    /**
     * Configuracion recomendada para diferentes tipos de impresoras
     */
    public function getRecommendedSettings(): array
    {
        return [
            'usb_printer' => [
                'description' => 'Impresora USB conectada directamente',
                'settings' => [
                    'PRINTING_ENABLED=true',
                    'PRINTING_TYPE=usb',
                    'PRINTING_PORT=/dev/usb/lp0',  // Linux
                    // 'PRINTING_PORT=COM1',        // Windows
                    'PRINTING_TIMEOUT=5'
                ]
            ],
            'serial_printer' => [
                'description' => 'Impresora conectada por puerto serial',
                'settings' => [
                    'PRINTING_ENABLED=true',
                    'PRINTING_TYPE=serial',
                    'PRINTING_PORT=/dev/ttyUSB0',  // Linux
                    // 'PRINTING_PORT=COM1',         // Windows
                    'PRINTING_BAUD_RATE=9600',
                    'PRINTING_DATA_BITS=8',
                    'PRINTING_STOP_BITS=1',
                    'PRINTING_PARITY=none'
                ]
            ],
            'network_printer' => [
                'description' => 'Impresora conectada por red (IP)',
                'settings' => [
                    'PRINTING_ENABLED=true',
                    'PRINTING_TYPE=network',
                    'PRINTING_HOST=192.168.1.100',
                    'PRINTING_NETWORK_PORT=9100',
                    'PRINTING_NETWORK_TIMEOUT=10'
                ]
            ]
        ];
    }

    /**
     * Ejemplo de formato de ticket que se genera
     */
    public function getTicketPreview(int $invoiceId): string
    {
        $invoice = Invoice::with(['warehouse', 'invoiceItems.item'])->findOrFail($invoiceId);
        $company = Company::getOrCreateCompany();

        $preview = "================================\n";
        $preview .= "        " . $company->name_company . "\n";
        $preview .= "      RIF: " . $company->dni . "\n";
        $preview .= wordwrap($company->address, 32, "\n", true) . "\n";
        $preview .= "        " . $company->phone . "\n";
        $preview .= "================================\n";
        $preview .= "            FACTURA\n";
        $preview .= "No: " . $invoice->code . "\n";
        $preview .= "Fecha: " . $invoice->created_at->format('d/m/Y H:i') . "\n";
        $preview .= "Almacen: " . $invoice->warehouse->name . "\n";
        $preview .= "--------------------------------\n";

        foreach ($invoice->invoiceItems as $item) {
            $preview .= wordwrap($item->item->name, 32, "\n", true) . "\n";
            
            $qty = number_format($item->amount, 2);
            $price = '$' . number_format($item->price, 2);
            $subtotal = '$' . number_format($item->subtotal, 2);
            
            $itemLine = $qty . ' x ' . $price;
            $spaces = 32 - strlen($itemLine) - strlen($subtotal);
            $itemLine .= str_repeat(' ', max(1, $spaces)) . $subtotal;
            
            $preview .= $itemLine . "\n";
        }

        $preview .= "--------------------------------\n";

        // Total - formato diferente si hay tasa de cambio
        if ($invoice->should_show_rate) {
            // Cuando hay tasa, mostrar primero el total en bolívares
            $totalBsText = 'TOTAL Bs: ' . number_format($invoice->total_amount_bs, 2);
            $preview .= str_repeat(' ', 32 - strlen($totalBsText)) . $totalBsText . "\n";

            // Luego el total de referencia en dólares (en minúsculas)
            $totalRefText = 'total ref: $' . number_format($invoice->total_amount, 2);
            $preview .= str_repeat(' ', 32 - strlen($totalRefText)) . $totalRefText . "\n";

            // Finalmente la tasa
            $preview .= 'Tasa: ' . number_format($invoice->rate, 4) . "\n";
        } else {
            // Cuando no hay tasa, mostrar solo el total en dólares
            $totalText = 'TOTAL: $' . number_format($invoice->total_amount, 2);
            $preview .= str_repeat(' ', 32 - strlen($totalText)) . $totalText . "\n";
        }

        $preview .= "================================\n";
        $preview .= "      Gracias por su compra!\n";
        $preview .= "\n\n";

        return $preview;
    }

    /**
     * Ejemplo de como normalizar texto con acentos
     */
    public function demonstrateTextNormalization(): array
    {
        $examples = [
            'Empresa: Panadería "El Buen Sabor"' => $this->printService->normalizeText('Empresa: Panadería "El Buen Sabor"'),
            'Almacén Principal' => $this->printService->normalizeText('Almacén Principal'),
            'Descripción del producto' => $this->printService->normalizeText('Descripción del producto'),
            '¡Gracias por su compra!' => $this->printService->normalizeText('¡Gracias por su compra!'),
            'Configuración avanzada' => $this->printService->normalizeText('Configuración avanzada'),
            'Niño pequeño' => $this->printService->normalizeText('Niño pequeño'),
        ];

        return [
            'description' => 'Ejemplos de normalización de texto para impresión térmica',
            'examples' => $examples,
            'usage' => [
                'Para normalizar cualquier texto antes de imprimir:',
                '$printService = new PrintService();',
                '$normalizedText = $printService->normalizeText("Texto con acentós");',
                'echo $normalizedText; // "Texto con acentos"'
            ]
        ];
    }

    /**
     * Comandos utiles para configurar impresoras en Linux
     */
    public function getLinuxCommands(): array
    {
        return [
            'list_usb_devices' => 'lsusb',
            'list_serial_ports' => 'ls /dev/tty*',
            'check_printer_permissions' => 'ls -la /dev/usb/lp*',
            'add_user_to_lp_group' => 'sudo usermod -a -G lp $USER',
            'test_printer_connection' => 'echo "Test" > /dev/usb/lp0',
            'check_printer_status' => 'lpstat -p',
        ];
    }
}
