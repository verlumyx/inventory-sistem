<?php

namespace App\Inventory\Entry\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Entry\Handlers\UpdateEntryHandler;
use App\Inventory\Entry\Handlers\GetEntryHandler;
use App\Inventory\Entry\Requests\UpdateEntryRequest;
use App\Inventory\Item\Contracts\ItemRepositoryInterface;
use App\Inventory\Warehouse\Contracts\WarehouseRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UpdateEntryController extends Controller
{
    public function __construct(
        private UpdateEntryHandler $updateEntryHandler,
        private GetEntryHandler $getEntryHandler,
        private ItemRepositoryInterface $itemRepository,
        private WarehouseRepositoryInterface $warehouseRepository
    ) {}

    /**
     * Show the form for editing the specified entry.
     */
    public function edit(int $id): Response
    {
        try {
            // Obtener la entrada con sus items
            $entry = $this->getEntryHandler->handleWithItems($id);

            // Verificar que la entrada no esté recibida
            if ($entry->status === 1) {
                return redirect()
                    ->route('entries.show', $id)
                    ->withErrors(['error' => 'No se puede editar una entrada que ya ha sido recibida.']);
            }

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
                ];
            });

            // Formatear datos de la entrada para el formulario
            $entryData = [
                'id' => $entry->id,
                'code' => $entry->code,
                'name' => $entry->name,
                'description' => $entry->description,
                'status' => $entry->status,
                'created_at' => $entry->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $entry->updated_at->format('Y-m-d H:i:s'),
                'items' => $entry->entryItems->map(function ($entryItem) {
                    return [
                        'id' => $entryItem->id,
                        'item_id' => $entryItem->item_id,
                        'warehouse_id' => $entryItem->warehouse_id,
                        'amount' => $entryItem->amount,
                        'item' => $entryItem->item ? [
                            'id' => $entryItem->item->id,
                            'code' => $entryItem->item->code,
                            'name' => $entryItem->item->name,
                            'display_name' => $entryItem->item->display_name,
                            'unit' => $entryItem->item->unit,
                        ] : null,
                        'warehouse' => $entryItem->warehouse ? [
                            'id' => $entryItem->warehouse->id,
                            'code' => $entryItem->warehouse->code,
                            'name' => $entryItem->warehouse->name,
                            'display_name' => $entryItem->warehouse->display_name,
                        ] : null,
                    ];
                }),
            ];

            return Inertia::render('entries/Edit', [
                'entry' => $entryData,
                'items' => $items,
                'warehouses' => $warehouses,
            ]);

        } catch (\Exception $e) {
            return Inertia::render('Error', [
                'status' => 404,
                'message' => 'Entrada no encontrada',
            ]);
        }
    }

    /**
     * Update the specified entry in storage.
     */
    public function update(UpdateEntryRequest $request, int $id): RedirectResponse
    {
        try {
            $validatedData = $request->getValidatedData();
            
            // Si se proporcionan items, actualizar con items, sino solo la entrada
            if ($validatedData['items'] !== null) {
                $entry = $this->updateEntryHandler->handleWithItems(
                    $id,
                    $validatedData['entry'],
                    $validatedData['items']
                );
            } else {
                $entry = $this->updateEntryHandler->handle($id, $validatedData['entry']);
            }

            return redirect()
                ->route('entries.show', $entry->id)
                ->with('success', "Entrada '{$entry->name}' actualizada exitosamente.");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar la entrada: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified entry via API.
     */
    public function updateApi(UpdateEntryRequest $request, int $id): JsonResponse
    {
        try {
            $validatedData = $request->getValidatedData();
            
            // Si se proporcionan items, actualizar con items, sino solo la entrada
            if ($validatedData['items'] !== null) {
                $entry = $this->updateEntryHandler->handleWithItems(
                    $id,
                    $validatedData['entry'],
                    $validatedData['items']
                );
            } else {
                $entry = $this->updateEntryHandler->handle($id, $validatedData['entry']);
            }

            return response()->json([
                'data' => $entry->toApiArray(),
                'message' => "Entrada '{$entry->name}' actualizada exitosamente.",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar la entrada',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle the status of the specified entry.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $entry = $this->updateEntryHandler->handleToggleStatus($id);

            return response()->json([
                'data' => $entry->toApiArray(),
                'message' => "Estado de entrada cambiado a " . ($entry->status ? 'activo' : 'inactivo') . ".",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cambiar el estado de la entrada',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate the specified entry.
     */
    public function activate(int $id): JsonResponse
    {
        try {
            $entry = $this->updateEntryHandler->handleActivate($id);

            return response()->json([
                'data' => $entry->toApiArray(),
                'message' => "Entrada '{$entry->name}' activada exitosamente.",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al activar la entrada',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deactivate the specified entry.
     */
    public function deactivate(int $id): JsonResponse
    {
        try {
            $entry = $this->updateEntryHandler->handleDeactivate($id);

            return response()->json([
                'data' => $entry->toApiArray(),
                'message' => "Entrada '{$entry->name}' desactivada exitosamente.",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al desactivar la entrada',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark an entry as received.
     */
    public function receive(int $id): RedirectResponse
    {
        try {
            $entry = $this->updateEntryHandler->handleReceive($id);

            return redirect()
                ->route('entries.show', $entry->id)
                ->with('success', "Entrada '{$entry->name}' marcada como recibida exitosamente.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Error al recibir la entrada: ' . $e->getMessage()]);
        }
    }
}
