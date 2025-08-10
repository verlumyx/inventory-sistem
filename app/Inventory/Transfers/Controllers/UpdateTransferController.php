<?php

namespace App\Inventory\Transfers\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Item\Models\Item;
use App\Inventory\Transfers\Handlers\UpdateTransferHandler;
use App\Inventory\Transfers\Requests\UpdateTransferRequest;
use App\Inventory\Warehouse\Models\Warehouse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class UpdateTransferController extends Controller
{
    public function __construct(private UpdateTransferHandler $updateTransferHandler) {}

    public function edit(int $id): Response|RedirectResponse
    {
        try {
            $t = app(\App\Inventory\Transfers\Handlers\GetTransferHandler::class)->handleById($id);
        } catch (\RuntimeException $e) {
            return redirect()->route('transfers.index')
                ->with('error', "El traslado con ID {$id} no existe.");
        }

        // Verificar que el traslado no esté completado
        if ($t->status === 1) {
            return redirect()->route('transfers.show', $id)
                ->withErrors(['error' => 'No se puede editar un traslado completado.']);
        }

        $warehouses = Warehouse::active()->orderBy('name')->get()->map(fn($w) => [
            'id' => $w->id,
            'code' => $w->code,
            'name' => $w->name,
            'display_name' => $w->display_name,
            'default' => $w->default,
        ]);

        $items = Item::active()->orderBy('name')->get()->map(fn($i) => [
            'id' => $i->id,
            'code' => $i->code,
            'name' => $i->name,
            'display_name' => $i->display_name,
            'unit' => $i->unit,
        ]);

        return Inertia::render('transfers/Edit', [
            'transfer' => [
                'id' => $t->id,
                'code' => $t->code,
                'description' => $t->description,
                'warehouse_id' => $t->warehouse_id,
                'warehouse_source_id' => $t->warehouse_source_id,
                'warehouse_destination_id' => $t->warehouse_destination_id,
                'status' => $t->status,
                'items' => $t->items->map(fn($it) => [
                    'id' => $it->id,
                    'item_id' => $it->item_id,
                    'amount' => $it->amount,
                ]),
            ],
            'warehouses' => $warehouses,
            'items' => $items,
        ]);
    }

    public function update(UpdateTransferRequest $request, int $id)
    {
        try {
            // Verificar que el traslado no esté completado antes de actualizar
            $transfer = app(\App\Inventory\Transfers\Handlers\GetTransferHandler::class)->handleById($id);
            if ($transfer->status === 1) {
                return redirect()->route('transfers.show', $id)
                    ->withErrors(['error' => 'No se puede editar un traslado completado.']);
            }

            $data = $request->validated();
            $transfer = $this->updateTransferHandler->handle($id, $data);

            return redirect()->route('transfers.show', $transfer->id)
                ->with('success', "Traslado '{$transfer->code}' actualizado exitosamente.");
        } catch (\RuntimeException $e) {
            return redirect()->route('transfers.index')
                ->with('error', "El traslado con ID {$id} no existe.");
        }
    }
}

