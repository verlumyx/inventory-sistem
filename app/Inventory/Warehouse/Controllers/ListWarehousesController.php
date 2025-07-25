<?php

namespace App\Inventory\Warehouse\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Warehouse\Handlers\ListWarehousesHandler;
use App\Inventory\Warehouse\Requests\ListWarehousesRequest;
use Inertia\Inertia;
use Inertia\Response;

class ListWarehousesController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private ListWarehousesHandler $listWarehousesHandler
    ) {}

    /**
     * Get paginated list of warehouses.
     *
     * @param ListWarehousesRequest $request
     * @return Response
     */
    public function __invoke(ListWarehousesRequest $request): Response
    {
        try {
            $validated = $request->validated();

            // Debug log
            \Log::info('Warehouse filters received:', $validated);

            // Extraer parámetros de paginación
            $perPage = $validated['per_page'] ?? 15;

            // Preparar filtros
            $filters = array_filter([
                'search' => $validated['search'] ?? null,
                'status' => isset($validated['status']) ? filter_var($validated['status'], FILTER_VALIDATE_BOOLEAN) : null,
                'name' => $validated['name'] ?? null,
                'code' => $validated['code'] ?? null,
            ], function($value) {
                return $value !== null;
            });

            $warehouses = $this->listWarehousesHandler->handlePaginated($filters, $perPage);

            \Log::info('Sending filters to frontend:', $filters);

            return Inertia::render('warehouses/Index', [
                'warehouses' => $warehouses->getCollection()->map(function ($warehouse) {
                    return [
                        'id' => $warehouse->id,
                        'code' => $warehouse->code,
                        'name' => $warehouse->name,
                        'description' => $warehouse->description,
                        'status' => $warehouse->status,
                        'status_text' => $warehouse->status_text,
                        'default' => $warehouse->default,
                        'default_text' => $warehouse->default_text,
                        'created_at' => $warehouse->created_at->toISOString(),
                        'updated_at' => $warehouse->updated_at->toISOString(),
                    ];
                }),
                'pagination' => [
                    'current_page' => $warehouses->currentPage(),
                    'last_page' => $warehouses->lastPage(),
                    'per_page' => $warehouses->perPage(),
                    'total' => $warehouses->total(),
                    'from' => $warehouses->firstItem(),
                    'to' => $warehouses->lastItem(),
                    'has_more_pages' => $warehouses->hasMorePages(),
                ],
                'filters' => $filters,
            ]);

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Error al cargar los almacenes. Por favor, inténtalo de nuevo.'
            ]);
        }
    }
}
