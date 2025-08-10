<?php

namespace App\Inventory\Adjustments\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Adjustments\Handlers\UpdateAdjustmentHandler;
use App\Inventory\Adjustments\Handlers\GetAdjustmentHandler;
use App\Inventory\Adjustments\Requests\UpdateAdjustmentRequest;
use App\Inventory\Warehouse\Models\Warehouse;
use App\Inventory\Item\Models\Item;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UpdateAdjustmentController extends Controller
{
    public function __construct(
        private UpdateAdjustmentHandler $updateHandler,
        private GetAdjustmentHandler $getHandler
    ) {}

    public function edit(int $id): Response|RedirectResponse
    {
        $adjustment = $this->getHandler->handleWithItems($id);

        // Verificar que el ajuste se pueda editar (solo si está pendiente)
        if ($adjustment->status !== 0) {
            return redirect()
                ->route('adjustments.show', $adjustment->id)
                ->with('error', 'No se puede editar un ajuste que ya ha sido aplicado.');
        }

        $warehouses = Warehouse::active()->orderBy('name')->get()->map(fn($w) => [
            'id' => $w->id,
            'code' => $w->code,
            'name' => $w->name,
            'display_name' => $w->display_name,
        ]);

        $items = Item::active()->orderBy('name')->get()->map(fn($i) => [
            'id' => $i->id,
            'code' => $i->code,
            'name' => $i->name,
            'unit' => $i->unit,
            'display_name' => $i->display_name,
        ]);

        return Inertia::render('adjustments/Edit', [
            'adjustment' => $adjustment->toApiArray(),
            'warehouses' => $warehouses,
            'items' => $items,
        ]);
    }

    public function update(UpdateAdjustmentRequest $request, int $id): RedirectResponse
    {
        // Verificar que el ajuste se pueda editar antes de procesar
        $adjustment = $this->getHandler->handleWithItems($id);
        if ($adjustment->status !== 0) {
            return redirect()
                ->route('adjustments.show', $adjustment->id)
                ->with('error', 'No se puede editar un ajuste que ya ha sido aplicado.');
        }

        $data = $request->getData();
        $items = $request->getItems();

        if (!empty($items)) {
            $adjustment = $this->updateHandler->handleWithItems($id, $data, $items);
        } else {
            $adjustment = $this->updateHandler->handle($id, $data);
        }

        return redirect()
            ->route('adjustments.show', $adjustment->id)
            ->with('success', "Ajuste '{$adjustment->code}' actualizado exitosamente.");
    }
}

