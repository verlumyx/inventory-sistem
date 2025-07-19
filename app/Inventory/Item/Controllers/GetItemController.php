<?php

namespace App\Inventory\Item\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Item\Handlers\GetItemHandler;
use App\Inventory\Item\Requests\GetItemRequest;
use App\Inventory\Item\Exceptions\ItemNotFoundException;
use Inertia\Inertia;
use Inertia\Response;

class GetItemController extends Controller
{
    public function __construct(
        private GetItemHandler $getItemHandler
    ) {}

    /**
     * Display the specified item.
     */
    public function __invoke(GetItemRequest $request, int $id): Response
    {
        try {
            $validated = $request->validated();
            $includeMetadata = isset($validated['include']) && $validated['include'] === 'metadata';

            if ($includeMetadata) {
                $data = $this->getItemHandler->handleForDisplay($id);
                $item = $data['item'];
                $metadata = $data['metadata'];
            } else {
                $itemModel = $this->getItemHandler->handle($id);
                $item = $itemModel->toApiArray();
                $metadata = null;
            }

            return Inertia::render('items/Show', [
                'item' => $item,
                'metadata' => $metadata,
            ]);

        } catch (ItemNotFoundException $e) {
            return Inertia::render('Errors/404', [
                'message' => $e->getMessage(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading item: ' . $e->getMessage());
            
            return Inertia::render('Errors/500', [
                'message' => 'Error al cargar el item. Por favor, intente nuevamente.',
            ]);
        }
    }
}
