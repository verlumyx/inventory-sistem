<?php

namespace App\Inventory\Item\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Item\Handlers\UpdateItemHandler;
use App\Inventory\Item\Handlers\GetItemHandler;
use App\Inventory\Item\Requests\UpdateItemRequest;
use App\Inventory\Item\Exceptions\ItemNotFoundException;
use App\Inventory\Item\Exceptions\ItemCodeAlreadyExistsException;
use App\Inventory\Item\Exceptions\ItemQrCodeAlreadyExistsException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UpdateItemController extends Controller
{
    public function __construct(
        private UpdateItemHandler $updateItemHandler,
        private GetItemHandler $getItemHandler
    ) {}

    /**
     * Show the form for editing the specified item.
     */
    public function edit(int $id): Response
    {
        try {
            $item = $this->getItemHandler->handle($id);

            return Inertia::render('items/Edit', [
                'item' => [
                    'id' => $item->id,
                    'code' => $item->code,
                    'name' => $item->name,
                    'qr_code' => $item->qr_code,
                    'description' => $item->description,
                    'price' => $item->price,
                    'unit' => $item->unit,
                    'status' => $item->status,
                    'created_at' => $item->created_at->toISOString(),
                    'updated_at' => $item->updated_at->toISOString(),
                ],
            ]);

        } catch (ItemNotFoundException $e) {
            return Inertia::render('Errors/404', [
                'message' => $e->getMessage(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading item for edit: ' . $e->getMessage());
            
            return Inertia::render('Errors/500', [
                'message' => 'Error al cargar el item. Por favor, intente nuevamente.',
            ]);
        }
    }

    /**
     * Update the specified item in storage.
     */
    public function update(UpdateItemRequest $request, int $id): RedirectResponse
    {
        try {
            $validated = $request->validated();
            
            $item = $this->updateItemHandler->handle($id, $validated);

            return redirect()
                ->route('items.show', $item->id)
                ->with('success', "Item '{$item->name}' actualizado exitosamente.");

        } catch (ItemNotFoundException $e) {
            return redirect()
                ->route('items.index')
                ->withErrors(['general' => $e->getMessage()]);

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
            \Log::error('Error updating item: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['general' => 'Error al actualizar el item. Por favor, intente nuevamente.']);
        }
    }
}
