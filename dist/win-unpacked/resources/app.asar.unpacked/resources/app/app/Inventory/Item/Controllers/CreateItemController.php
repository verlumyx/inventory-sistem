<?php

namespace App\Inventory\Item\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Item\Handlers\CreateItemHandler;
use App\Inventory\Item\Requests\CreateItemRequest;
use App\Inventory\Item\Exceptions\ItemCodeAlreadyExistsException;
use App\Inventory\Item\Exceptions\ItemQrCodeAlreadyExistsException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CreateItemController extends Controller
{
    public function __construct(
        private CreateItemHandler $createItemHandler
    ) {}

    /**
     * Show the form for creating a new item.
     */
    public function create(): Response
    {
        return Inertia::render('items/Create');
    }

    /**
     * Store a newly created item in storage.
     */
    public function store(CreateItemRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            
            $item = $this->createItemHandler->handle($validated);

            return redirect()
                ->route('items.show', $item->id)
                ->with('success', "Item '{$item->name}' creado exitosamente.");

        } catch (ItemCodeAlreadyExistsException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['code' => $e->getMessage()]);

        } catch (ItemQrCodeAlreadyExistsException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['qr_code' => $e->getMessage()]);

        } catch (\Exception $e) {
            \Log::error('Error creating item: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['general' => 'Error al crear el item. Por favor, intente nuevamente.']);
        }
    }
}
