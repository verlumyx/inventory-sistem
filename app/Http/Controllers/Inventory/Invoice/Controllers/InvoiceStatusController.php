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
     * Generar PDF visual para vista previa (58mm)
     */
    private function generateVisualPdf($invoice): string
    {
        // Crear instancia de FPDF con tamaño personalizado para 58mm
        $pdf = new FPDF('P', 'mm', [58, 200]); // 58mm de ancho, altura variable
        $pdf->AddPage();
        $pdf->SetMargins(2, 2, 2); // Márgenes mínimos
        $pdf->SetAutoPageBreak(true, 2);

        // Configurar fuente
        $pdf->SetFont('Arial', 'B', 8);

        // Título centrado
        $pdf->Cell(54, 4, 'SISTEMA DE INVENTARIO', 0, 1, 'C');
        $pdf->Ln(2);

        // Información de la empresa
        $pdf->SetFont('Arial', '', 6);
        $company = $invoice->company;
        if ($company) {
            $pdf->Cell(54, 3, $company->name_company, 0, 1, 'C');
            $pdf->Cell(54, 3, 'RIF: ' . $company->dni, 0, 1, 'C');
            $pdf->Cell(54, 3, $company->phone, 0, 1, 'C');
        }
        $pdf->Ln(2);

        // Línea separadora
        $pdf->Cell(54, 1, str_repeat('=', 32), 0, 1, 'C');
        $pdf->Ln(1);

        // Información de la factura
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(54, 3, 'FACTURA: ' . $invoice->code, 0, 1, 'L');
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(54, 3, 'Fecha: ' . $invoice->created_at->format('d/m/Y H:i:s'), 0, 1, 'L');
        $pdf->Cell(54, 3, 'Cliente: ' . $invoice->customer_name, 0, 1, 'L');
        $pdf->Ln(2);

        // Items
        if ($invoice->items && count($invoice->items) > 0) {
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->Cell(54, 3, 'PRODUCTOS:', 0, 1, 'L');
            $pdf->Cell(54, 1, str_repeat('-', 32), 0, 1, 'L');

            $pdf->SetFont('Arial', '', 5);
            foreach ($invoice->items as $item) {
                // Nombre del producto (puede ocupar múltiples líneas)
                $productName = $this->wrapTextForPdf($item->product_name, 32);
                $pdf->Cell(54, 2.5, $productName, 0, 1, 'L');

                // Cantidad y precio en una línea
                $qty = number_format($item->quantity, 2);
                $price = number_format($item->unit_price, 2);
                $total = number_format($item->total_price, 2);

                $line = sprintf('%s x $%s = $%s', $qty, $price, $total);
                $pdf->Cell(54, 2.5, $line, 0, 1, 'R');
                $pdf->Ln(0.5);
            }
        }

        $pdf->Ln(1);
        $pdf->Cell(54, 1, str_repeat('=', 32), 0, 1, 'C');

        // Totales
        $pdf->SetFont('Arial', 'B', 7);
        if ($invoice->rate && $invoice->rate > 0) {
            // Mostrar subtotal, tasa y total cuando hay tasa
            $subtotal = $invoice->subtotal ?? $invoice->total_amount ?? 0;
            $pdf->Cell(54, 3, 'SUBTOTAL: $' . number_format($subtotal, 2), 0, 1, 'R');
            $pdf->Cell(54, 3, 'TASA: ' . number_format($invoice->rate, 4), 0, 1, 'R');
            $pdf->Cell(54, 3, 'TOTAL Bs: ' . number_format($invoice->total_amount ?? 0, 2), 0, 1, 'R');
        } else {
            // Solo mostrar total en dólares cuando no hay tasa
            $pdf->Cell(54, 3, 'TOTAL: $' . number_format($invoice->total_amount ?? 0, 2), 0, 1, 'R');
        }

        $pdf->Ln(2);
        $pdf->Cell(54, 1, str_repeat('=', 32), 0, 1, 'C');
        $pdf->Ln(1);

        // Pie de página
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(54, 3, 'Gracias por su compra!', 0, 1, 'C');

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
}
