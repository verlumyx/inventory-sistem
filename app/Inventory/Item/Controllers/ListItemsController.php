<?php

namespace App\Inventory\Item\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Item\Handlers\ListItemsHandler;
use App\Inventory\Item\Requests\ListItemsRequest;
use Inertia\Inertia;
use Inertia\Response;

class ListItemsController extends Controller
{
    public function __construct(
        private ListItemsHandler $listItemsHandler
    ) {}

    /**
     * Display a listing of items.
     */
    public function __invoke(ListItemsRequest $request): Response
    {
        try {
            $validated = $request->validated();

            // Debug log
            \Log::info('Item filters received:', $validated);

            // Extraer parámetros de paginación
            $perPage = $validated['per_page'] ?? 15;

            // Preparar filtros
            $filters = array_filter([
                'search' => $validated['search'] ?? null,
                'status' => isset($validated['status']) ? filter_var($validated['status'], FILTER_VALIDATE_BOOLEAN) : null,
                'name' => $validated['name'] ?? null,
                'code' => $validated['code'] ?? null,
                'qr_code' => $validated['qr_code'] ?? null,
            ], function($value) {
                return $value !== null;
            });

            $items = $this->listItemsHandler->handlePaginated($filters, $perPage);

            \Log::info('Sending filters to frontend:', $filters);

            return Inertia::render('items/Index', [
                'items' => $items->getCollection()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'code' => $item->code,
                        'name' => $item->name,
                        'qr_code' => $item->qr_code,
                        'description' => $item->description,
                        'price' => $item->price,
                        'unit' => $item->unit,
                        'status' => $item->status,
                        'status_text' => $item->status_text,
                        'created_at' => $item->created_at->toISOString(),
                        'updated_at' => $item->updated_at->toISOString(),
                    ];
                }),
                'pagination' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'per_page' => $items->perPage(),
                    'total' => $items->total(),
                    'from' => $items->firstItem(),
                    'to' => $items->lastItem(),
                    'has_more_pages' => $items->hasMorePages(),
                ],
                'filters' => $filters,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error listing items: ' . $e->getMessage());
            
            return Inertia::render('items/Index', [
                'items' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $perPage ?? 15,
                    'total' => 0,
                    'from' => null,
                    'to' => null,
                    'has_more_pages' => false,
                ],
                'filters' => [],
                'error' => 'Error al cargar los items. Por favor, intente nuevamente.',
            ]);
        }
    }
}
