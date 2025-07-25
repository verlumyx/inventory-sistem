<?php

namespace App\Inventory\Invoice\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Invoice\Handlers\CreateInvoiceHandler;
use App\Inventory\Invoice\Requests\CreateInvoiceRequest;
use App\Inventory\Invoice\Exceptions\InvoiceValidationException;
use App\Inventory\Invoice\Exceptions\InvoiceOperationException;
use App\Inventory\Warehouse\Models\Warehouse;
use App\Inventory\Item\Models\Item;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CreateInvoiceController extends Controller
{
    public function __construct(
        private CreateInvoiceHandler $createInvoiceHandler
    ) {}

    /**
     * Show the form for creating a new invoice.
     */
    public function create(): Response
    {
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

        return Inertia::render('invoices/Create', [
            'warehouses' => $warehouses,
            'items' => $items,
            'defaultWarehouse' => $defaultWarehouse ? [
                'id' => $defaultWarehouse->id,
                'code' => $defaultWarehouse->code,
                'name' => $defaultWarehouse->name,
                'display_name' => $defaultWarehouse->display_name,
            ] : null,
        ]);
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(CreateInvoiceRequest $request): RedirectResponse
    {
        try {
            $invoiceData = $request->getInvoiceData();
            $itemsData = $request->getItemsData();
            
            $invoice = $this->createInvoiceHandler->handleWithItems($invoiceData, $itemsData);

            return redirect()
                ->route('invoices.show', $invoice->id)
                ->with('success', "Factura '{$invoice->code}' creada exitosamente.");

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
            \Log::error('Error creating invoice: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['general' => 'Error al crear la factura. Por favor, intente nuevamente.']);
        }
    }
}
