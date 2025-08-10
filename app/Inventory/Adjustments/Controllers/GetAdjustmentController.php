<?php

namespace App\Inventory\Adjustments\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Adjustments\Handlers\GetAdjustmentHandler;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GetAdjustmentController extends Controller
{
    public function __construct(
        private GetAdjustmentHandler $getHandler
    ) {}

    public function __invoke(int $id): RedirectResponse|Response
    {
        try {
            $adjustment = $this->getHandler->handleWithItems($id);

            return Inertia::render('adjustments/Show', [
                'adjustment' => array_merge($adjustment->toApiArray(), [
                    'items' => $adjustment->items->map(fn($it) => $it->toApiArray()),
                ]),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('adjustments.index')
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}

