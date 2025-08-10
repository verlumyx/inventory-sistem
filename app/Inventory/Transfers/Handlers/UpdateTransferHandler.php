<?php

namespace App\Inventory\Transfers\Handlers;

use App\Inventory\Transfers\Contracts\TransferRepositoryInterface;
use App\Inventory\Transfers\Models\Transfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateTransferHandler
{
    public function __construct(private TransferRepositoryInterface $transferRepository) {}

    public function handle(int $id, array $data): Transfer
    {
        return DB::transaction(function () use ($id, $data) {
            $transfer = $this->transferRepository->findById($id);
            if (!$transfer) {
                throw new \RuntimeException("Traslado con ID {$id} no encontrado.");
            }

            $prevStatus = (int)$transfer->status;

            $this->transferRepository->update($transfer, [
                'description' => $data['description'] ?? $transfer->description,
                'warehouse_id' => $data['warehouse_id'] ?? $transfer->warehouse_id,
                'warehouse_source_id' => $data['warehouse_source_id'] ?? $transfer->warehouse_source_id,
                'warehouse_destination_id' => $data['warehouse_destination_id'] ?? $transfer->warehouse_destination_id,
                'status' => (int)($data['status'] ?? $transfer->status),
            ]);

            if (isset($data['items'])) {
                // Reemplazar items (simple por ahora)
                $transfer->items()->delete();
                foreach ($data['items'] as $item) {
                    $transfer->items()->create([
                        'item_id' => $item['item_id'],
                        'amount' => $item['amount'],
                    ]);
                }
            }

            $transfer->refresh();

            // Cambios de estado: 0->1 (completar) o 1->0 (revertir)
            if ($prevStatus !== (int)$transfer->status) {
                if ((int)$transfer->status === 1) {
                    app(\App\Inventory\Transfers\Services\TransferStockValidator::class)->validateForCompletion($transfer->load(['items.item']));
                    app(\App\Inventory\Transfers\Handlers\TransferInventoryHandler::class)->handleCompletion($transfer->load(['items']));
                } elseif ($prevStatus === 1 && (int)$transfer->status === 0) {
                    app(\App\Inventory\Transfers\Handlers\TransferInventoryHandler::class)->handleRevert($transfer->load(['items']));
                }
            }

            Log::info('Traslado actualizado', ['transfer_id' => $transfer->id, 'status' => $transfer->status]);
            return $transfer->fresh(['items.item', 'sourceWarehouse', 'destinationWarehouse']);
        });
    }
}

