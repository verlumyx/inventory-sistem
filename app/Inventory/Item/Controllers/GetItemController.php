<?php

namespace App\Inventory\Item\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Item\Handlers\GetItemHandler;
use App\Inventory\Item\Requests\GetItemRequest;
use App\Inventory\Item\Exceptions\ItemNotFoundException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class GetItemController extends Controller
{
    public function __construct(
        private GetItemHandler $getItemHandler
    ) {}

    /**
     * Display the specified item.
     */
    public function __invoke(GetItemRequest $request, int $id): Response|RedirectResponse
    {
        try {
            $validated = $request->validated();
            $includeMetadata = isset($validated['include']) && $validated['include'] === 'metadata';

            if ($includeMetadata) {
                $data = $this->getItemHandler->handleForDisplay($id);
                $item = $data['item'];
                $metadata = $data['metadata'];
            } else {
                $itemModel = $this->getItemHandler->handle($id);
                $item = $itemModel->toApiArray();
                $metadata = null;
            }

            // Obtener información de warehouses con stock
            $warehousesWithStock = $itemModel->warehousesWithStock()
                ->select('warehouses.id', 'warehouses.code', 'warehouses.name', 'warehouse_items.quantity_available')
                ->get()
                ->map(function ($warehouse) {
                    return [
                        'id' => $warehouse->id,
                        'code' => $warehouse->code,
                        'name' => $warehouse->name,
                        'display_name' => $warehouse->code . ' - ' . $warehouse->name,
                        'quantity_available' => (float) $warehouse->pivot->quantity_available,
                    ];
                });

            // Calcular stock total
            $totalStock = $itemModel->total_stock;

            return Inertia::render('items/Show', [
                'item' => $item,
                'metadata' => $metadata,
                'warehouses' => $warehousesWithStock,
                'totalStock' => $totalStock,
            ]);

        } catch (ItemNotFoundException $e) {
            return redirect()->route('items.index')
                ->with('error', "El artículo con ID {$id} no existe.");

        } catch (\Exception $e) {
            \Log::error('Error loading item: ' . $e->getMessage());

            return redirect()->route('items.index')
                ->with('error', 'Error al cargar el artículo. Por favor, intente nuevamente.');
        }
    }
}
