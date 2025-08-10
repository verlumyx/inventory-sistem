<?php

namespace App\Inventory\Transfers\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Transfers\Handlers\GetTransferHandler;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class GetTransferController extends Controller
{
    public function __construct(private GetTransferHandler $getTransferHandler) {}

    public function __invoke(int $transfer): Response|RedirectResponse
    {
        try {
            $t = $this->getTransferHandler->handleById($transfer);

            return Inertia::render('transfers/Show', [
                'transfer' => [
                    'id' => $t->id,
                    'code' => $t->code,
                    'description' => $t->description,
                    'warehouse_id' => $t->warehouse_id,
                    'warehouse_source_id' => $t->warehouse_source_id,
                    'warehouse_destination_id' => $t->warehouse_destination_id,
                    'status' => $t->status,
                    'status_text' => $t->status === 1 ? 'Completado' : 'Pendiente',
                    'created_at' => $t->created_at?->toISOString(),
                    'updated_at' => $t->updated_at?->toISOString(),
                    'items' => $t->items->map(fn($it) => [
                        'id' => $it->id,
                        'item_id' => $it->item_id,
                        'amount' => $it->amount,
                        'formatted_amount' => number_format($it->amount, 2) . ' ' . ($it->item->unit ?? 'pcs'),
                        'item' => [
                            'id' => $it->item->id,
                            'code' => $it->item->code,
                            'name' => $it->item->name,
                            'display_name' => $it->item->display_name,
                            'unit' => $it->item->unit,
                        ],
                    ]),
                    'source' => $t->sourceWarehouse?->toApiArray(),
                    'destination' => $t->destinationWarehouse?->toApiArray(),
                ],
            ]);
        } catch (\RuntimeException $e) {
            return redirect()->route('transfers.index')
                ->with('error', "El traslado con ID {$transfer} no existe.");
        }
    }
}

