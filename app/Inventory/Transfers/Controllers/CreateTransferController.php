<?php

namespace App\Inventory\Transfers\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Item\Models\Item;
use App\Inventory\Transfers\Handlers\CreateTransferHandler;
use App\Inventory\Transfers\Requests\CreateTransferRequest;
use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CreateTransferController extends Controller
{
    public function __construct(private CreateTransferHandler $createTransferHandler) {}

    public function create(): Response
    {
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

        return Inertia::render('transfers/Create', compact('warehouses', 'items'));
    }

    public function store(CreateTransferRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $transfer = $this->createTransferHandler->handle($data);

        return redirect()->route('transfers.show', $transfer->id)
            ->with('success', "Traslado '{$transfer->code}' creado exitosamente.");
    }
}

