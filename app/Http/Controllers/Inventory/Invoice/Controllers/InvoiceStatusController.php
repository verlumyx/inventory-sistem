<?php

namespace App\Http\Controllers\Inventory\Invoice\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Invoice\Handlers\GetInvoiceHandler;
use App\Inventory\Invoice\Handlers\InvoiceInventoryHandler;
use App\Inventory\Invoice\Services\InvoiceStockValidator;
use App\Inventory\Invoice\Exceptions\InvoiceNotFoundException;
use App\Inventory\Invoice\Exceptions\InsufficientStockException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class InvoiceStatusController extends Controller
{
    public function __construct(
        private readonly GetInvoiceHandler $getInvoiceHandler,
        private readonly InvoiceInventoryHandler $invoiceInventoryHandler,
        private InvoiceStockValidator $stockValidator
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
}
