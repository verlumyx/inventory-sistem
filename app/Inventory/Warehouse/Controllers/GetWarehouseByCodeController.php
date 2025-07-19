<?php

namespace App\Inventory\Warehouse\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Warehouse\Exceptions\WarehouseNotFoundException;
use App\Inventory\Warehouse\Handlers\GetWarehouseHandler;
use Inertia\Inertia;
use Inertia\Response;

class GetWarehouseByCodeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private GetWarehouseHandler $getWarehouseHandler
    ) {}

    /**
     * Get a warehouse by code.
     *
     * @param string $code
     * @return Response
     */
    public function __invoke(string $code): Response
    {
        try {
            $warehouse = $this->getWarehouseHandler->handleByCode($code);

            return Inertia::render('Warehouses/Show', [
                'warehouse' => [
                    'id' => $warehouse->id,
                    'code' => $warehouse->code,
                    'name' => $warehouse->name,
                    'description' => $warehouse->description,
                    'status' => $warehouse->status,
                    'status_text' => $warehouse->status_text,
                    'created_at' => $warehouse->created_at->toISOString(),
                    'updated_at' => $warehouse->updated_at->toISOString(),
                ]
            ]);

        } catch (WarehouseNotFoundException $e) {
            return redirect()->route('warehouses.index')
                ->withErrors(['error' => $e->getMessage()]);

        } catch (\Exception $e) {
            return redirect()->route('warehouses.index')
                ->withErrors(['error' => 'Error al cargar el almacén. Por favor, inténtalo de nuevo.']);
        }
    }
}
