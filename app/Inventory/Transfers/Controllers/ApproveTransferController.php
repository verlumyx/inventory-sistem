<?php

namespace App\Inventory\Transfers\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Transfers\Handlers\GetTransferHandler;
use App\Inventory\Transfers\Handlers\TransferInventoryHandler;
use App\Inventory\Transfers\Services\TransferStockValidator;
use App\Inventory\Transfers\Contracts\TransferRepositoryInterface;
use App\Inventory\Transfers\Exceptions\InsufficientStockException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApproveTransferController extends Controller
{
    public function __construct(
        private GetTransferHandler $getTransferHandler,
        private TransferRepositoryInterface $transferRepository,
        private TransferStockValidator $stockValidator,
        private TransferInventoryHandler $inventoryHandler
    ) {}

    public function __invoke(int $transferId): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($transferId) {
                // Obtener el traslado
                $transfer = $this->getTransferHandler->handleById($transferId);
                
                if (!$transfer) {
                    return redirect()->back()
                        ->withErrors(['error' => 'Traslado no encontrado.']);
                }

                // Verificar que el traslado esté pendiente
                if ($transfer->status !== 0) {
                    return redirect()->back()
                        ->withErrors(['error' => 'Solo se pueden aprobar traslados pendientes.']);
                }

                // Validar que hay suficiente stock en el almacén origen
                $this->stockValidator->validateForCompletion($transfer);

                // Mover el inventario de origen a destino
                $this->inventoryHandler->handleCompletion($transfer);

                // Actualizar el estado del traslado a completado
                $this->transferRepository->update($transfer, [
                    'status' => 1
                ]);

                Log::info('Traslado aprobado exitosamente', [
                    'transfer_id' => $transfer->id,
                    'transfer_code' => $transfer->code,
                    'source_warehouse' => $transfer->warehouse_source_id,
                    'destination_warehouse' => $transfer->warehouse_destination_id,
                    'items_count' => $transfer->items->count(),
                ]);

                return redirect()->route('transfers.show', $transfer->id)
                    ->with('success', "Traslado '{$transfer->code}' aprobado exitosamente. El inventario ha sido transferido.");
            });
        } catch (InsufficientStockException $e) {
            Log::warning('Traslado no aprobado por stock insuficiente', [
                'transfer_id' => $transferId,
                'stock_errors' => $e->getStockErrors()
            ]);

            return redirect()->back()
                ->withErrors(['error' => $e->getFormattedMessage()]);
        } catch (\Exception $e) {
            Log::error('Error al aprobar traslado', [
                'transfer_id' => $transferId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Error al aprobar el traslado: ' . $e->getMessage()]);
        }
    }
}
