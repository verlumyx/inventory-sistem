<?php

namespace App\Inventory\Adjustments\Handlers;

use App\Inventory\Adjustments\Contracts\AdjustmentRepositoryInterface;
use App\Inventory\Adjustments\Models\Adjustment;
use Illuminate\Support\Facades\Log;

class GetAdjustmentHandler
{
    public function __construct(
        private AdjustmentRepositoryInterface $repository
    ) {}

    public function handleById(int $id): Adjustment
    {
        $model = $this->repository->findById($id);
        if (!$model) {
            Log::warning('Ajuste no encontrado', ['id' => $id]);
            throw new \RuntimeException("Ajuste con ID {$id} no encontrado");
        }
        return $model;
    }

    public function handleByCode(string $code): Adjustment
    {
        $model = $this->repository->findByCode($code);
        if (!$model) {
            Log::warning('Ajuste no encontrado por código', ['code' => $code]);
            throw new \RuntimeException("Ajuste con código {$code} no encontrado");
        }
        return $model;
    }

    public function handleWithItems(int $id): Adjustment
    {
        return $this->handleById($id)->load(['items.item', 'warehouse']);
    }
}

