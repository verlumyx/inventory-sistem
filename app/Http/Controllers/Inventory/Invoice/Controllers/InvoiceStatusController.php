<?php

namespace App\Http\Controllers\Inventory\Invoice\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Invoice\Handlers\GetInvoiceHandler;
use App\Inventory\Invoice\Handlers\InvoiceInventoryHandler;
use App\Inventory\Invoice\Services\InvoiceStockValidator;
use App\Inventory\Invoice\Exceptions\InvoiceNotFoundException;
use App\Inventory\Invoice\Exceptions\InsufficientStockException;
use App\Services\PrintService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use FPDF;

class InvoiceStatusController extends Controller
{
    public function __construct(
        private readonly GetInvoiceHandler $getInvoiceHandler,
        private readonly InvoiceInventoryHandler $invoiceInventoryHandler,
        private InvoiceStockValidator $stockValidator,
        private PrintService $printService
    ) {}

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(int $id): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                // Obtener la factura con sus items
                $invoice = $this->getInvoiceHandler->handleWithItems($id);

                if ($invoice->is_paid) {
                    return redirect()
                        ->route('invoices.show', $id)
                        ->withErrors(['error' => 'La factura ya está marcada como pagada.']);
                }

                // Validar que hay suficiente stock antes de marcar como pagada
                $this->stockValidator->validateStockForPayment($invoice);

                // Marcar como pagada
                $invoice->markAsPaid();

                // Actualizar inventario (reducir stock)
                $this->invoiceInventoryHandler->handlePaidInventoryUpdate($invoice);

                return redirect()
                    ->route('invoices.show', $id)
                    ->with('success', "Factura '{$invoice->code}' marcada como pagada exitosamente. El inventario ha sido actualizado.");
            });

        } catch (InvoiceNotFoundException $e) {
            return redirect()->route('invoices.index')
                ->withErrors(['error' => $e->getMessage()]);

        } catch (InsufficientStockException $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getFormattedMessage()]);

        } catch (\Exception $e) {
            \Log::error('Error marking invoice as paid: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withErrors(['error' => 'Error al marcar la factura como pagada. Por favor, intente nuevamente.']);
        }
    }

    /**
     * Mark invoice as pending.
     */
    public function markAsPending(int $id): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                // Obtener la factura con sus items
                $invoice = $this->getInvoiceHandler->handleWithItems($id);

                if ($invoice->is_pending) {
                    return redirect()
                        ->route('invoices.show', $id)
                        ->withErrors(['error' => 'La factura ya está marcada como por pagar.']);
                }

                // Marcar como pendiente
                $invoice->markAsPending();

                // Actualizar inventario (devolver stock)
                $this->invoiceInventoryHandler->handlePendingInventoryUpdate($invoice);

                return redirect()
                    ->route('invoices.show', $id)
                    ->with('success', "Factura '{$invoice->code}' marcada como por pagar exitosamente. El inventario ha sido restaurado.");
            });

        } catch (InvoiceNotFoundException $e) {
            return redirect()->route('invoices.index')
                ->withErrors(['error' => $e->getMessage()]);

        } catch (\Exception $e) {
            \Log::error('Error marking invoice as pending: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withErrors(['error' => 'Error al marcar la factura como por pagar. Por favor, intente nuevamente.']);
        }
    }

    /**
     * Print invoice.
     */
    public function print(int $id): RedirectResponse
    {
        try {
            // Obtener la factura con sus relaciones
            $invoice = $this->getInvoiceHandler->handleWithItems($id);

            // Verificar que la factura esté pagada
            if (!$invoice->is_paid) {
                return redirect()
                    ->back()
                    ->withErrors(['error' => 'Solo se pueden imprimir facturas pagadas.']);
            }

            // Verificar que la impresión esté disponible
            if (!$this->printService->isAvailable()) {
                return redirect()
                    ->back()
                    ->withErrors(['error' => 'La impresora no está disponible. Verifique la configuración de impresión.']);
            }

            // Imprimir la factura
            $result = $this->printService->printInvoice($invoice);

            if ($result) {
                \Log::info('Factura impresa exitosamente', [
                    'invoice_id' => $invoice->id,
                    'invoice_code' => $invoice->code,
                    'user_id' => auth()->id()
                ]);

                return redirect()
                    ->back()
                    ->with('success', "Factura '{$invoice->code}' impresa exitosamente.");
            } else {
                return redirect()
                    ->back()
                    ->withErrors(['error' => 'Error al enviar la factura a la impresora.']);
            }

        } catch (InvoiceNotFoundException $e) {
            return redirect()->route('invoices.index')
                ->withErrors(['error' => 'Factura no encontrada.']);

        } catch (\Exception $e) {
            \Log::error('Error printing invoice', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'Error al imprimir la factura: ' . $e->getMessage()]);
        }
    }

    /**
     * Generar vista previa PDF visual para navegador (58mm)
     */
    public function generateThermalPdfPreview(int $id): Response
    {
        try {
            // Obtener la factura con sus relaciones
            $invoice = $this->getInvoiceHandler->handleWithItems($id);

            // Agregar alias para items (compatibilidad con PrintService)
            $invoice->setRelation('items', $invoice->invoiceItems);

            // Verificar que la factura esté pagada
            if (!$invoice->is_paid) {
                abort(403, 'Solo se pueden generar tickets de facturas pagadas.');
            }

            // Generar PDF visual para vista previa
            $pdf = $this->generateVisualPdf($invoice);

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="preview_' . $invoice->code . '_58mm.pdf"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (InvoiceNotFoundException $e) {
            abort(404, 'Factura no encontrada.');

        } catch (\Exception $e) {
            \Log::error('Error generando vista previa PDF térmica', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            abort(500, 'Error al generar la vista previa PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generar comandos ESC/POS puros para impresión directa (58mm)
     */
    public function generateThermalPrint(int $id): Response
    {
        try {
            // Obtener la factura con sus relaciones
            $invoice = $this->getInvoiceHandler->handleWithItems($id);

            // Verificar que la factura esté pagada
            if (!$invoice->is_paid) {
                abort(403, 'Solo se pueden generar tickets de facturas pagadas.');
            }

            // Generar contenido del ticket térmico con comandos ESC/POS
            $ticket = $this->generateThermalTicketContent($invoice);

            return response($ticket, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="print_' . $invoice->code . '_58mm.escpos"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (InvoiceNotFoundException $e) {
            abort(404, 'Factura no encontrada.');

        } catch (\Exception $e) {
            \Log::error('Error generando comandos ESC/POS', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            abort(500, 'Error al generar comandos ESC/POS: ' . $e->getMessage());
        }
    }

    /**
     * Generar contenido del ticket térmico con comandos ESC/POS
     */
    private function generateThermalTicketContent($invoice): string
    {
        $content = '';

        // Comandos ESC/POS para impresora térmica
        $ESC = chr(27);
        $GS = chr(29);

        // Inicializar impresora
        $content .= $ESC . "@"; // Inicializar

        // Configurar codificación UTF-8
        $content .= $ESC . "t" . chr(16); // Seleccionar tabla de caracteres

        // Título centrado y en negrita
        $content .= $ESC . "a" . chr(1); // Centrar
        $content .= $ESC . "E" . chr(1); // Negrita ON
        $content .= "SISTEMA DE INVENTARIO\n";
        $content .= $ESC . "E" . chr(0); // Negrita OFF
        $content .= "\n";

        // Fecha y hora
        $content .= "Fecha: " . date('d/m/Y H:i:s') . "\n";
        $content .= "\n";

        // Información de la factura
        $content .= $ESC . "E" . chr(1); // Negrita ON
        $content .= "FACTURA: " . $invoice->code . "\n";
        $content .= $ESC . "E" . chr(0); // Negrita OFF
        $content .= "\n";

        // Línea separadora
        $content .= str_repeat("-", 32) . "\n";

        // Alinear a la izquierda
        $content .= $ESC . "a" . chr(0);

        // Información del almacén
        $content .= "Almacen: " . $invoice->warehouse->name . "\n";
        $content .= "Estado: " . $invoice->status_text . "\n";

        if ($invoice->customer) {
            $content .= "Cliente: " . $invoice->customer->name . "\n";
        }

        $content .= "\n";

        // Items de la factura
        if ($invoice->items && count($invoice->items) > 0) {
            $content .= $ESC . "E" . chr(1); // Negrita ON
            $content .= "PRODUCTOS:\n";
            $content .= $ESC . "E" . chr(0); // Negrita OFF
            $content .= str_repeat("-", 32) . "\n";

            foreach ($invoice->items as $item) {
                $productName = $item->product->name ?? 'Producto';
                // Truncar nombre si es muy largo
                if (strlen($productName) > 20) {
                    $productName = substr($productName, 0, 17) . '...';
                }

                $content .= $productName . "\n";
                $content .= sprintf("  %d x $%.2f = $%.2f\n",
                    $item->quantity,
                    $item->unit_price,
                    $item->total_price
                );
            }

            $content .= str_repeat("-", 32) . "\n";

            // Total
            $content .= $ESC . "a" . chr(2); // Alinear derecha
            $content .= $ESC . "E" . chr(1); // Negrita ON
            $content .= sprintf("TOTAL: $%.2f\n", $invoice->total_amount);
            $content .= $ESC . "E" . chr(0); // Negrita OFF
            $content .= $ESC . "a" . chr(0); // Alinear izquierda
        }

        $content .= "\n";

        // Mensaje final centrado
        $content .= $ESC . "a" . chr(1); // Centrar
        $content .= "Gracias por su compra\n";
        $content .= "\n\n\n";

        // Cortar papel
        $content .= $GS . "V" . chr(1); // Corte parcial

        return $content;
    }

    /**
     * Generar PDF visual para vista previa (58mm) - Estructura igual al PrintService
     */
    private function generateVisualPdf($invoice): string
    {
        // Crear instancia de FPDF con tamaño personalizado para 58mm
        $pdf = new FPDF('P', 'mm', [58, 250]); // 58mm de ancho, altura aumentada
        $pdf->AddPage();
        $pdf->SetMargins(1, 2, 1); // Márgenes laterales reducidos (1mm en lugar de 2mm)
        $pdf->SetAutoPageBreak(true, 2);

        // Obtener datos de la empresa
        $company = $invoice->company ?? \App\Models\Company::getOrCreateCompany();

        // 1. CABECERA DE LA EMPRESA (igual que PrintService líneas 135-140)
        $pdf->SetFont('Arial', '', 8); // Fuente más grande
        $pdf->Cell(0, 4,trim($this->normalizeText($company->name_company)), 0, 1, 'L'); // Usar ancho completo (0)
        $pdf->Cell(0, 4, 'RIF: ' . $this->normalizeText($company->dni), 0, 1, 'L'); // Usar ancho completo (0)
        $pdf->Cell(0, 4, $this->normalizeText($company->address), 0, 1, 'L'); // Usar ancho completo (0)
        $pdf->Cell(0, 4, $this->normalizeText($company->phone), 0, 1, 'L'); // Usar ancho completo (0)
        $pdf->Ln(2); // Más espacio
        $pdf->Cell(0, 1, str_repeat('=', 32), 0, 1, 'L'); // Usar ancho completo (0)
        $pdf->Ln(2); // Más espacio

        // 2. INFORMACIÓN DE LA FACTURA (igual que PrintService líneas 142-147)
        $pdf->SetFont('Arial', 'B', 9); // Fuente más grande
        $pdf->Cell(0, 4, 'FACTURA', 0, 1, 'C'); // Usar ancho completo (0) para centrar
        $pdf->Ln(2); // Más espacio

        $pdf->SetFont('Arial', '', 8); // Fuente más grande
        $pdf->Cell(0, 4, 'No: ' . $invoice->code, 0, 1, 'L'); // Usar ancho completo (0)
        $pdf->Cell(0, 4, 'Fecha: ' . $invoice->created_at->format('d/m/Y H:i'), 0, 1, 'L'); // Usar ancho completo (0)
        $pdf->Cell(0, 4, 'Almacen: ' . $this->normalizeText($invoice->warehouse->name), 0, 1, 'L'); // Usar ancho completo (0)
        $pdf->Ln(2); // Más espacio
        $pdf->Cell(0, 1, str_repeat('=', 32), 0, 1, 'L'); // Usar ancho completo (0)
        $pdf->Ln(2); // Más espacio

        // 3. ITEMS (igual que PrintService líneas 149-176)
        $items = $invoice->items ?? $invoice->invoiceItems ?? collect();
        $pdf->SetFont('Arial', '', 7); // Fuente más grande

        foreach ($items as $item) {
            // Nombre del item
            $itemName = $item->item->name ?? $item->product_name ?? 'Item';
            $pdf->Cell(0, 3.5, $this->normalizeText($this->wrapTextForPdf($itemName, 32)), 0, 1, 'L'); // Usar ancho completo (0)

            // Cantidad x Precio = Subtotal (lógica exacta del PrintService)
            $qty = number_format($item->amount ?? $item->quantity ?? 0, 2);

            if ($invoice->should_show_rate) {
                // Cuando hay tasa, mostrar precios en bolívares
                $priceInBs = ($item->price ?? $item->unit_price ?? 0) * $invoice->rate;
                $subtotalInBs = ($item->subtotal ?? $item->total_price ?? 0) * $invoice->rate;

                $price = 'Bs' . number_format($priceInBs, 2);
                $subtotal = 'Bs' . number_format($subtotalInBs, 2);
            } else {
                // Sin tasa, mostrar precios en dólares
                $price = '$' . number_format($item->price ?? $item->unit_price ?? 0, 2);
                $subtotal = '$' . number_format($item->subtotal ?? $item->total_price ?? 0, 2);
            }

            // Formato exacto del PrintService: "qty x price    subtotal"
            $itemLine = $qty . ' x ' . $price;
            $spaces = 32 - strlen($itemLine) - strlen($subtotal);
            $itemLine .= str_repeat(' ', max(1, $spaces)) . $subtotal;

            $pdf->Cell(0, 3.5, $itemLine, 0, 1, 'L'); // Usar ancho completo (0)
        }
        $pdf->Ln(2); // Más espacio

        $pdf->Cell(0, 1, str_repeat('=', 32), 0, 1, 'L'); // Usar ancho completo (0)
        $pdf->Ln(2); // Más espacio

        // 4. TOTALES (igual que PrintService líneas 180-196)
        if ($invoice->should_show_rate) {
            // Cuando hay tasa, mostrar primero el total en bolívares
            $pdf->SetFont('Arial', 'B', 9); // Fuente más grande
            $totalBsText = 'TOTAL Bs: ' . number_format($invoice->total_amount_bs, 2);
            $pdf->Cell(0, 4, $totalBsText, 0, 1, 'R'); // Usar ancho completo (0)

            // Luego el total de referencia en dólares (en minúsculas)
            $pdf->SetFont('Arial', '', 8); // Fuente más grande
            $totalRefText = 'total ref: $' . number_format($invoice->total_amount, 2);
            $pdf->Cell(0, 4, $totalRefText, 0, 1, 'R'); // Usar ancho completo (0)

            // Finalmente la tasa
            $pdf->Cell(0, 4, 'Tasa: ' . number_format($invoice->rate, 4), 0, 1, 'L'); // Usar ancho completo (0)
        } else {
            // Cuando no hay tasa, mostrar solo el total en dólares
            $pdf->SetFont('Arial', 'B', 9); // Fuente más grande
            $totalText = 'TOTAL: $' . number_format($invoice->total_amount, 2);
            $pdf->Cell(0, 4, $totalText, 0, 1, 'R'); // Usar ancho completo (0)
        }
        $pdf->Ln(2); // Más espacio

        $pdf->Cell(0, 1, str_repeat('=', 32), 0, 1, 'C'); // Usar ancho completo (0) y centrar

        // 5. PIE DE PÁGINA (igual que PrintService líneas 200-203)
        $pdf->SetFont('Arial', '', 8); // Fuente más grande
        $pdf->Cell(0, 4, $this->normalizeText('Gracias por su compra!'), 0, 1, 'C'); // Usar ancho completo (0)

        // Más espacio al final para poder romper el ticket
        $pdf->Ln(5); // Más espacio
        $pdf->Ln(5); // Más espacio
        $pdf->Ln(5); // Más espacio
        $pdf->Ln(5); // Más espacio para romper fácilmente
        $pdf->Cell(56, 1, str_repeat('=', 32), 0, 1, 'L');

        return $pdf->Output('S'); // Retornar como string
    }

    /**
     * Ajustar texto para PDF (truncar si es muy largo)
     */
    private function wrapTextForPdf(string $text, int $maxLength): string
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }

        return substr($text, 0, $maxLength - 3) . '...';
    }

    /**
     * Normalizar texto para impresión térmica (igual que PrintService)
     * Elimina acentos y caracteres especiales que pueden causar problemas
     */
    private function normalizeText(string $text): string
    {
        // Mapa de caracteres con acentos a sin acentos (igual que PrintService)
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
        return strtr($text, $replacements);
    }
}
