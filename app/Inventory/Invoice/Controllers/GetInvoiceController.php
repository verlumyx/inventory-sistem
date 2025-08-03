<?php

namespace App\Inventory\Invoice\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Invoice\Handlers\GetInvoiceHandler;
use App\Inventory\Invoice\Exceptions\InvoiceNotFoundException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GetInvoiceController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private GetInvoiceHandler $getInvoiceHandler
    ) {}

    /**
     * Display the specified invoice.
     */
    public function __invoke(int $id): RedirectResponse|Response
    {
        try {
            $invoice = $this->getInvoiceHandler->handleWithItems($id);

            return Inertia::render('invoices/Show', [
                'invoice' => [
                    'id' => $invoice->id,
                    'code' => $invoice->code,
                    'warehouse_id' => $invoice->warehouse_id,
                    'status' => $invoice->status,
                    'status_text' => $invoice->status_text,
                    'is_pending' => $invoice->is_pending,
                    'is_paid' => $invoice->is_paid,
                    'can_edit' => $invoice->can_edit,
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
                            'formatted_amount' => $invoiceItem->formatted_amount,
                            'formatted_price' => $invoiceItem->formatted_price,
                            'formatted_subtotal' => $invoiceItem->formatted_subtotal,
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
                    'formatted_total_amount' => '$' . number_format($invoice->total_amount, 2),
                    'rate' => $invoice->rate ?? 1.0000,
                    'formatted_rate' => $invoice->formatted_rate,
                    'should_show_rate' => $invoice->should_show_rate,
                    'total_amount_bs' => $invoice->total_amount_bs,
                    'formatted_total_amount_bs' => number_format($invoice->total_amount_bs, 2) . ' Bs',
                    'items_count' => $invoice->items_count,
                    'display_name' => $invoice->display_name,
                    'created_at' => $invoice->created_at->toISOString(),
                    'updated_at' => $invoice->updated_at->toISOString(),
                ]
            ]);

        } catch (InvoiceNotFoundException $e) {
            return redirect()->route('invoices.index')
                ->withErrors(['error' => $e->getMessage()]);

        } catch (\Exception $e) {
            return redirect()->route('invoices.index')
                ->withErrors(['error' => 'Error al cargar la factura. Por favor, inténtalo de nuevo.']);
        }
    }
}
