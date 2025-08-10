<?php

namespace App\Inventory\Transfers\Handlers;

use App\Inventory\Transfers\Contracts\TransferRepositoryInterface;
use App\Inventory\Transfers\Models\Transfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateTransferHandler
{
    public function __construct(private TransferRepositoryInterface $transferRepository) {}

    public function handle(array $data): Transfer
    {
        return DB::transaction(function () use ($data) {
            try {
                // Crear traslado
                $transfer = $this->transferRepository->create([
                    'description' => $data['description'] ?? null,
                    'warehouse_id' => $data['warehouse_id'] ?? null,
                    'warehouse_source_id' => $data['warehouse_source_id'],
                    'warehouse_destination_id' => $data['warehouse_destination_id'],
                    'status' => (int)($data['status'] ?? 0),
                ]);

                // Crear items
                foreach ($data['items'] as $item) {
                    $transfer->items()->create([
                        'item_id' => $item['item_id'],
                        'amount' => $item['amount'],
                    ]);
                }

                // Si viene como completado, validar y mover inventario
                if ($transfer->status === 1) {
                    app(\App\Inventory\Transfers\Services\TransferStockValidator::class)->validateForCompletion($transfer->fresh(['items.item']));
                    app(\App\Inventory\Transfers\Handlers\TransferInventoryHandler::class)->handleCompletion($transfer->fresh(['items']));
                }

                Log::info('Traslado creado exitosamente', [
                    'transfer_id' => $transfer->id,
                    'transfer_code' => $transfer->code,
                    'items_count' => count($data['items'] ?? []),
                ]);

                return $transfer;
            } catch (\Exception $e) {
                Log::error('Error al crear traslado', ['error' => $e->getMessage(), 'data' => $data]);
                throw $e;
            }
        });
    }
}

