<?php

namespace App\Inventory\Adjustments\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Adjustments\Contracts\AdjustmentRepositoryInterface;
use App\Inventory\Adjustments\Services\AdjustmentInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdjustmentStatusController extends Controller
{
    public function __construct(
        private AdjustmentRepositoryInterface $repository,
        private AdjustmentInventoryService $inventoryService
    ) {}

    public function markAsApplied(int $id): RedirectResponse
    {
        try {
            $adjustment = $this->repository->findById($id);
            if (!$adjustment) {
                return redirect()->back()->with('error', 'Ajuste no encontrado.');
            }

            if ($adjustment->status === 1) {
                return redirect()->back()->with('info', 'El ajuste ya está aplicado.');
            }

            // Cargar los items del ajuste
            $adjustment->load(['items.item', 'warehouse']);

            return DB::transaction(function () use ($adjustment) {
                // Aplicar el ajuste al inventario
                $inventoryResult = $this->inventoryService->applyAdjustment($adjustment);

                if (!$inventoryResult['success']) {
                    // Si hay errores, mostrarlos al usuario
                    $errorMessage = 'No se pudo aplicar el ajuste: ' . implode(', ', $inventoryResult['errors']);
                    throw new \RuntimeException($errorMessage);
                }

                // Si todo salió bien, marcar el ajuste como aplicado
                $this->repository->update($adjustment, ['status' => 1]);

                Log::info('Ajuste aplicado exitosamente', [
                    'adjustment_id' => $adjustment->id,
                    'code' => $adjustment->code,
                    'type' => $adjustment->type,
                    'inventory_results' => $inventoryResult['results']
                ]);

                return redirect()
                    ->route('adjustments.show', $adjustment->id)
                    ->with('success', "Ajuste '{$adjustment->code}' aplicado exitosamente al inventario.");
            });

        } catch (\Exception $e) {
            Log::error('Error al aplicar ajuste', [
                'adjustment_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', $e->getMessage());
        }
    }


}
