<?php

namespace App\Inventory\Transfers\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Item\Models\Item;
use App\Inventory\Transfers\Handlers\UpdateTransferHandler;
use App\Inventory\Transfers\Requests\UpdateTransferRequest;
use App\Inventory\Warehouse\Models\Warehouse;
use Inertia\Inertia;
use Inertia\Response;

class UpdateTransferController extends Controller
{
    public function __construct(private UpdateTransferHandler $updateTransferHandler) {}

    public function edit(int $id): Response
    {
        $t = app(\App\Inventory\Transfers\Handlers\GetTransferHandler::class)->handleById($id);

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
        $data = $request->validated();
        $transfer = $this->updateTransferHandler->handle($id, $data);

        return redirect()->route('transfers.show', $transfer->id)
            ->with('success', "Traslado '{$transfer->code}' actualizado exitosamente.");
    }
}

