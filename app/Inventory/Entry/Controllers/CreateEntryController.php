<?php

namespace App\Inventory\Entry\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Entry\Handlers\CreateEntryHandler;
use App\Inventory\Entry\Requests\CreateEntryRequest;
use App\Inventory\Item\Contracts\ItemRepositoryInterface;
use App\Inventory\Warehouse\Contracts\WarehouseRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CreateEntryController extends Controller
{
    public function __construct(
        private CreateEntryHandler $createEntryHandler,
        private ItemRepositoryInterface $itemRepository,
        private WarehouseRepositoryInterface $warehouseRepository
    ) {}

    /**
     * Show the form for creating a new entry.
     */
    public function create(): Response
    {
        // Obtener items y almacenes activos para los selectores
        $items = $this->itemRepository->getActive()->map(function ($item) {
            return [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'display_name' => $item->display_name,
                'price' => $item->price,
                'unit' => $item->unit,
            ];
        });

        $warehouses = $this->warehouseRepository->getActive()->map(function ($warehouse) {
            return [
                'id' => $warehouse->id,
                'code' => $warehouse->code,
                'name' => $warehouse->name,
                'display_name' => $warehouse->display_name,
                'default' => $warehouse->default,
            ];
        });

        // Obtener el almacén por defecto
        $defaultWarehouse = $this->warehouseRepository->getActive()->where('default', true)->first();

        return Inertia::render('entries/Create', [
            'items' => $items,
            'warehouses' => $warehouses,
            'defaultWarehouse' => $defaultWarehouse ? [
                'id' => $defaultWarehouse->id,
                'code' => $defaultWarehouse->code,
                'name' => $defaultWarehouse->name,
                'display_name' => $defaultWarehouse->display_name,
            ] : null,
        ]);
    }

    /**
     * Store a newly created entry in storage.
     */
    public function store(CreateEntryRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->getValidatedData();

            $entry = $this->createEntryHandler->handleWithItems(
                $validatedData['entry'],
                $validatedData['items']
            );

            return redirect()
                ->route('entries.show', $entry->id)
                ->with('success', "Entrada '{$entry->name}' creada exitosamente.");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Error al crear la entrada: ' . $e->getMessage()]);
        }
    }

    /**
     * Store a newly created entry via API.
     */
    public function storeApi(CreateEntryRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->getValidatedData();
            
            $entry = $this->createEntryHandler->handleWithItems(
                $validatedData['entry'],
                $validatedData['items']
            );

            return response()->json([
                'data' => $entry->toApiArray(),
                'message' => "Entrada '{$entry->name}' creada exitosamente.",
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear la entrada',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate entry data.
     */
    public function validate(CreateEntryRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->getValidatedData();
            
            // Validar datos básicos
            $entryErrors = $this->createEntryHandler->validate($validatedData['entry']);
            
            // Validar items
            $itemsValid = $this->createEntryHandler->isItemsValid($validatedData['items']);

            $isValid = empty($entryErrors) && $itemsValid;

            return response()->json([
                'valid' => $isValid,
                'errors' => $entryErrors,
                'data' => $validatedData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'error' => 'Error de validación',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get form data for creating entry.
     */
    public function getFormData(): JsonResponse
    {
        try {
            // Obtener items activos
            $items = $this->itemRepository->getActive()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'name' => $item->name,
                    'display_name' => $item->display_name,
                    'price' => $item->price,
                    'unit' => $item->unit,
                    'description' => $item->description,
                ];
            });

            // Obtener almacenes activos
            $warehouses = $this->warehouseRepository->getActive()->map(function ($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'code' => $warehouse->code,
                    'name' => $warehouse->name,
                    'display_name' => $warehouse->display_name,
                    'description' => $warehouse->description,
                ];
            });

            return response()->json([
                'data' => [
                    'items' => $items,
                    'warehouses' => $warehouses,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener datos del formulario',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
