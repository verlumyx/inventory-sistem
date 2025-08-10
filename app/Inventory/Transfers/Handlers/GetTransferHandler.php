<?php

namespace App\Inventory\Transfers\Handlers;

use App\Inventory\Transfers\Contracts\TransferRepositoryInterface;
use App\Inventory\Transfers\Models\Transfer;
use Illuminate\Support\Facades\Log;

class GetTransferHandler
{
    public function __construct(private TransferRepositoryInterface $transferRepository) {}

    public function handleById(int $id): Transfer
    {
        $transfer = $this->transferRepository->findById($id);
        if (!$transfer) {
            throw new \RuntimeException("Traslado con ID {$id} no encontrado.");
        }
        Log::info('Traslado obtenido', ['transfer_id' => $transfer->id, 'transfer_code' => $transfer->code]);
        return $transfer;
    }
}

