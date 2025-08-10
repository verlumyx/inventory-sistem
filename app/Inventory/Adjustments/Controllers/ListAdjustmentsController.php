<?php

namespace App\Inventory\Adjustments\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Adjustments\Handlers\ListAdjustmentsHandler;
use App\Inventory\Adjustments\Requests\ListAdjustmentsRequest;
use Inertia\Inertia;
use Inertia\Response;

class ListAdjustmentsController extends Controller
{
    public function __construct(
        private ListAdjustmentsHandler $listHandler
    ) {}

    public function __invoke(ListAdjustmentsRequest $request): Response
    {
        $filters = $request->getFilters();
        $paginationParams = $request->getPaginationParams();

        $adjustments = $this->listHandler->handlePaginated($filters, $paginationParams['per_page']);

        $adjustmentsArray = collect($adjustments->items())->map(fn($a) => $a->toApiArray())->values();

        return Inertia::render('adjustments/Index', [
            'filters' => $filters,
            'pagination' => [
                'current_page' => $adjustments->currentPage(),
                'per_page' => $adjustments->perPage(),
                'total' => $adjustments->total(),
                'last_page' => $adjustments->lastPage(),
                'from' => $adjustments->firstItem() ?? 0,
                'to' => $adjustments->lastItem() ?? 0,
            ],
            'adjustments' => $adjustmentsArray,
        ]);
    }
}

