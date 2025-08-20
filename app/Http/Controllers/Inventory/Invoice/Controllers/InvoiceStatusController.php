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
use Illuminate\Support\Facades\DB;

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
}
