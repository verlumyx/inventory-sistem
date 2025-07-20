<?php

namespace App\Inventory\Warehouse\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Warehouse\Exceptions\WarehouseOperationException;
use App\Inventory\Warehouse\Exceptions\WarehouseValidationException;
use App\Inventory\Warehouse\Handlers\CreateWarehouseHandler;
use App\Inventory\Warehouse\Requests\CreateWarehouseRequest;
use Illuminate\Http\RedirectResponse;

class CreateWarehouseController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private CreateWarehouseHandler $createWarehouseHandler
    ) {}

    /**
     * Create a new warehouse.
     *
     * @param CreateWarehouseRequest $request
     * @return RedirectResponse
     */
    public function __invoke(CreateWarehouseRequest $request): RedirectResponse
    {
        try {
            $warehouse = $this->createWarehouseHandler->handle($request->validated());

            return redirect()->route('warehouses.show', $warehouse->id)
                ->with('success', "Almacén '{$warehouse->name}' creado exitosamente.");

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
