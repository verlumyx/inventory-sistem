<?php

namespace App\Inventory\Transfers\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Transfers\Handlers\ListTransfersHandler;
use App\Inventory\Transfers\Requests\ListTransfersRequest;
use App\Inventory\Transfers\Contracts\TransferRepositoryInterface;
use Inertia\Inertia;
use Inertia\Response;

class ListTransfersController extends Controller
{
    public function __construct(private ListTransfersHandler $listTransfersHandler, private TransferRepositoryInterface $transferRepo) {}

    public function __invoke(ListTransfersRequest $request): Response
    {
        $filters = $request->getFilters();
        $pagination = $request->getPaginationParams();

        $transfers = $this->listTransfersHandler->handlePaginated($filters, $pagination['per_page']);

        return Inertia::render('transfers/Index', [
            'transfers' => $transfers->through(function ($t) {
                return [
                    'id' => $t->id,
                    'code' => $t->code,
                    'description' => $t->description,
                    'status' => (int)$t->status,
                    'status_text' => ((int)$t->status === 1 ? 'Completado' : 'Pendiente'),
                    'source' => $t->sourceWarehouse?->display_name,
                    'destination' => $t->destinationWarehouse?->display_name,
                    'created_at' => $t->created_at?->format('Y-m-d H:i:s'),
                    'updated_at' => $t->updated_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'filters' => $filters,
            'pagination' => [
                'current_page' => $transfers->currentPage(),
                'last_page' => $transfers->lastPage(),
                'per_page' => $transfers->perPage(),
                'total' => $transfers->total(),
                'from' => $transfers->firstItem(),
                'to' => $transfers->lastItem(),
            ],
        ]);
    }
}

