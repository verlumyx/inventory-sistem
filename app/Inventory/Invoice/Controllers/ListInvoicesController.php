<?php

namespace App\Inventory\Invoice\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Invoice\Handlers\ListInvoicesHandler;
use App\Inventory\Invoice\Requests\ListInvoicesRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ListInvoicesController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private ListInvoicesHandler $listInvoicesHandler
    ) {}

    /**
     * Display a listing of the invoices.
     */
    public function __invoke(ListInvoicesRequest $request): Response
    {
        $filters = $request->getFilters();
        $paginationParams = $request->getPaginationParams();
        
        $invoices = $this->listInvoicesHandler->handlePaginated(
            $filters,
            $paginationParams['per_page']
        );

        // Formatear datos para la vista
        $formattedInvoices = $invoices->through(function ($invoice) {
            return [
                'id' => $invoice->id,
                'code' => $invoice->code,
                'warehouse_id' => $invoice->warehouse_id,
                'warehouse' => [
                    'id' => $invoice->warehouse->id,
                    'code' => $invoice->warehouse->code,
                    'name' => $invoice->warehouse->name,
                ],
                'total_amount' => $invoice->total_amount,
                'items_count' => $invoice->items_count,
                'display_name' => $invoice->display_name,
                'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $invoice->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return Inertia::render('invoices/Index', [
            'invoices' => $formattedInvoices,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
                'from' => $invoices->firstItem(),
                'to' => $invoices->lastItem(),
            ],
        ]);
    }

    /**
     * Get invoices for API.
     */
    public function api(ListInvoicesRequest $request): JsonResponse
    {
        $filters = $request->getFilters();
        $paginationParams = $request->getPaginationParams();
        
        $invoices = $this->listInvoicesHandler->handlePaginated(
            $filters,
            $paginationParams['per_page']
        );

        // Formatear datos para API
        $formattedInvoices = $invoices->through(function ($invoice) {
            return $invoice->toApiArray();
        });

        return response()->json([
            'data' => $formattedInvoices,
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
                'from' => $invoices->firstItem(),
                'to' => $invoices->lastItem(),
            ],
        ]);
    }
}
