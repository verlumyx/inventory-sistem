<?php

namespace App\Inventory\Warehouse\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Warehouse\Handlers\ListWarehousesHandler;
use Inertia\Inertia;
use Inertia\Response;

class ListActiveWarehousesController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private ListWarehousesHandler $listWarehousesHandler
    ) {}

    /**
     * Get list of active warehouses.
     *
     * @return Response
     */
    public function __invoke(): Response
    {
        try {
            $warehouses = $this->listWarehousesHandler->handleActive();

            return Inertia::render('warehouses/Active', [
                'warehouses' => $warehouses->map(function ($warehouse) {
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
                'total' => $warehouses->count(),
            ]);

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Error al cargar los almacenes activos. Por favor, inténtalo de nuevo.'
            ]);
        }
    }
}
