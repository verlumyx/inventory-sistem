<?php

namespace App\Inventory\Adjustments\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Adjustments\Handlers\CreateAdjustmentHandler;
use App\Inventory\Adjustments\Requests\CreateAdjustmentRequest;
use App\Inventory\Warehouse\Models\Warehouse;
use App\Inventory\Item\Models\Item;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CreateAdjustmentController extends Controller
{
    public function __construct(
        private CreateAdjustmentHandler $createHandler
    ) {}

    public function create(): Response
    {
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

        return Inertia::render('adjustments/Create', [
            'warehouses' => $warehouses,
            'items' => $items,
        ]);
    }

    public function store(CreateAdjustmentRequest $request): RedirectResponse
    {
        $data = $request->getData();
        $items = $request->getItems();

        // Si vienen items, crear el ajuste junto con sus items
        if (!empty($items)) {
            $adjustment = $this->createHandler->handleWithItems($data, $items);
        } else {
            $adjustment = $this->createHandler->handle($data);
        }

        return redirect()
            ->route('adjustments.show', $adjustment->id)
            ->with('success', "Ajuste '{$adjustment->code}' creado exitosamente.");
    }
}

