<?php

namespace App\Inventory\Warehouse\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Warehouse\Exceptions\WarehouseNotFoundException;
use App\Inventory\Warehouse\Exceptions\WarehouseOperationException;
use App\Inventory\Warehouse\Exceptions\WarehouseValidationException;
use App\Inventory\Warehouse\Handlers\UpdateWarehouseHandler;
use App\Inventory\Warehouse\Requests\UpdateWarehouseRequest;
use Illuminate\Http\RedirectResponse;

class UpdateWarehouseController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private UpdateWarehouseHandler $updateWarehouseHandler
    ) {}

    /**
     * Update a warehouse.
     *
     * @param UpdateWarehouseRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function __invoke(UpdateWarehouseRequest $request, int $id): RedirectResponse
    {
        try {
            $warehouse = $this->updateWarehouseHandler->handle($id, $request->validated());

            return redirect()->route('warehouses.index')
                ->with('success', 'Almacén actualizado exitosamente');

        } catch (WarehouseNotFoundException $e) {
            return redirect()->route('warehouses.index')
                ->withErrors(['error' => $e->getMessage()]);

        } catch (WarehouseValidationException $e) {
            return back()
                ->withErrors($e->getErrors())
                ->withInput();

        } catch (WarehouseOperationException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Error interno del servidor. Por favor, inténtalo de nuevo.'])
                ->withInput();
        }
    }
}
