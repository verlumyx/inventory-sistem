<?php

namespace App\Inventory\Invoice\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Invoice\Handlers\UpdateInvoiceHandler;
use App\Inventory\Invoice\Handlers\GetInvoiceHandler;
use App\Inventory\Invoice\Requests\UpdateInvoiceRequest;
use App\Inventory\Invoice\Exceptions\InvoiceNotFoundException;
use App\Inventory\Invoice\Exceptions\InvoiceValidationException;
use App\Inventory\Invoice\Exceptions\InvoiceOperationException;
use App\Inventory\Warehouse\Models\Warehouse;
use App\Inventory\Item\Models\Item;
use App\Inventory\ExchangeRate\Models\ExchangeRate;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UpdateInvoiceController extends Controller
{
    public function __construct(
        private UpdateInvoiceHandler $updateInvoiceHandler,
        private GetInvoiceHandler $getInvoiceHandler
    ) {}

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(int $id): Response|RedirectResponse
    {
        try {
            $invoice = $this->getInvoiceHandler->handleWithItems($id);

            // Verificar si la factura puede ser editada
            if ($invoice->is_paid) {
                return redirect()
                    ->route('invoices.show', $id)
                    ->withErrors(['error' => 'No se puede editar una factura que ya está pagada.']);
            }

            // Obtener warehouses activos para el select
            $warehouses = Warehouse::active()
                ->orderBy('name')
                ->get()
                ->map(function ($warehouse) {
                    return [
                        'id' => $warehouse->id,
                        'code' => $warehouse->code,
                        'name' => $warehouse->name,
                        'display_name' => $warehouse->display_name,
                        'default' => $warehouse->default,
                    ];
                });

            // Obtener el almacén por defecto
            $defaultWarehouse = Warehouse::active()->where('default', true)->first();

            // Obtener items activos para el formulario dinámico
            $items = Item::active()
                ->orderBy('name')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'code' => $item->code,
                        'name' => $item->name,
                        'price' => $item->price,
                        'unit' => $item->unit,
                        'display_name' => $item->display_name,
                    ];
                });

            return Inertia::render('invoices/Edit', [
                'invoice' => [
                    'id' => $invoice->id,
                    'code' => $invoice->code,
                    'warehouse_id' => $invoice->warehouse_id,
                    'warehouse' => [
                        'id' => $invoice->warehouse->id,
                        'code' => $invoice->warehouse->code,
                        'name' => $invoice->warehouse->name,
                        'display_name' => $invoice->warehouse->display_name,
                    ],
                    'items' => $invoice->invoiceItems->map(function ($invoiceItem) {
                        return [
                            'id' => $invoiceItem->id,
                            'item_id' => $invoiceItem->item_id,
                            'amount' => $invoiceItem->amount,
                            'price' => $invoiceItem->price,
                            'subtotal' => $invoiceItem->subtotal,
                            'item' => [
                                'id' => $invoiceItem->item->id,
                                'code' => $invoiceItem->item->code,
                                'name' => $invoiceItem->item->name,
                                'unit' => $invoiceItem->item->unit,
                                'display_name' => $invoiceItem->item->display_name,
                            ],
                        ];
                    }),
                    'total_amount' => $invoice->total_amount,
                    'items_count' => $invoice->items_count,
                    'rate' => $invoice->rate ?? 1.0000,
                    'formatted_rate' => $invoice->formatted_rate,
                    'should_show_rate' => $invoice->should_show_rate,
                    'total_amount_bs' => $invoice->total_amount_bs,
                    'created_at' => $invoice->created_at->toISOString(),
                    'updated_at' => $invoice->updated_at->toISOString(),
                ],
                'warehouses' => $warehouses,
                'items' => $items,
                'defaultWarehouse' => $defaultWarehouse ? [
                    'id' => $defaultWarehouse->id,
                    'code' => $defaultWarehouse->code,
                    'name' => $defaultWarehouse->name,
                    'display_name' => $defaultWarehouse->display_name,
                ] : null,
                'currentRate' => $invoice->rate ?? 1.0000,
                'shouldShowRate' => $invoice->should_show_rate,
            ]);

        } catch (InvoiceNotFoundException $e) {
            return redirect()->route('invoices.index')
                ->withErrors(['error' => $e->getMessage()]);

        } catch (\Exception $e) {
            return redirect()->route('invoices.index')
                ->withErrors(['error' => 'Error al cargar la factura para editar.']);
        }
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(UpdateInvoiceRequest $request, int $id): RedirectResponse
    {
        try {
            // Verificar que la factura existe y puede ser editada
            $invoice = $this->getInvoiceHandler->handleById($id);
            if ($invoice->is_paid) {
                return redirect()
                    ->route('invoices.show', $id)
                    ->withErrors(['error' => 'No se puede editar una factura que ya está pagada.']);
            }

            $invoiceData = $request->getInvoiceData();
            $itemsData = $request->getItemsData();

            if ($request->hasItems()) {
                $invoice = $this->updateInvoiceHandler->handleWithItems($id, $invoiceData, $itemsData);
            } else {
                $invoice = $this->updateInvoiceHandler->handle($id, $invoiceData);
            }

            return redirect()
                ->route('invoices.show', $invoice->id)
                ->with('success', "Factura '{$invoice->code}' actualizada exitosamente.");

        } catch (InvoiceNotFoundException $e) {
            return redirect()->route('invoices.index')
                ->withErrors(['error' => $e->getMessage()]);

        } catch (InvoiceValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->getErrors());

        } catch (InvoiceOperationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['general' => $e->getMessage()]);

        } catch (\Exception $e) {
            \Log::error('Error updating invoice: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['general' => 'Error al actualizar la factura. Por favor, intente nuevamente.']);
        }
    }
}
