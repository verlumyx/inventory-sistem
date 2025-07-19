<?php

namespace App\Inventory\Entry\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Entry\Handlers\GetEntryHandler;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class GetEntryController extends Controller
{
    public function __construct(
        private GetEntryHandler $getEntryHandler
    ) {}

    /**
     * Display the specified entry.
     */
    public function __invoke(int $id): Response
    {
        try {
            $displayData = $this->getEntryHandler->handleForDisplay($id);

            return Inertia::render('entries/Show', [
                'entry' => $displayData['entry'],
                'items' => $displayData['items'],
                'metadata' => $displayData['metadata'],
            ]);

        } catch (\Exception $e) {
            return Inertia::render('Error', [
                'status' => 404,
                'message' => 'Entrada no encontrada',
            ]);
        }
    }

    /**
     * Get the specified entry via API.
     */
    public function api(int $id): JsonResponse
    {
        try {
            $entry = $this->getEntryHandler->handle($id);

            return response()->json([
                'data' => $entry->toApiArray(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Entrada no encontrada',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get the specified entry with items via API.
     */
    public function withItems(int $id): JsonResponse
    {
        try {
            $entry = $this->getEntryHandler->handleWithItems($id);

            $entryData = $entry->toApiArray();
            $entryData['items'] = $entry->entryItems->map(function ($entryItem) {
                return $entryItem->toApiArray();
            });

            return response()->json([
                'data' => $entryData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Entrada no encontrada',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get entry by code via API.
     */
    public function byCode(string $code): JsonResponse
    {
        try {
            $entry = $this->getEntryHandler->handleByCode($code);

            return response()->json([
                'data' => $entry->toApiArray(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Entrada no encontrada',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Check if entry exists.
     */
    public function exists(int $id): JsonResponse
    {
        $exists = $this->getEntryHandler->exists($id);

        return response()->json([
            'exists' => $exists,
        ]);
    }

    /**
     * Check if entry exists by code.
     */
    public function existsByCode(string $code): JsonResponse
    {
        $exists = $this->getEntryHandler->existsByCode($code);

        return response()->json([
            'exists' => $exists,
        ]);
    }

    /**
     * Get entry for display with additional metadata.
     */
    public function forDisplay(int $id): JsonResponse
    {
        try {
            $displayData = $this->getEntryHandler->handleForDisplay($id);

            return response()->json([
                'data' => $displayData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Entrada no encontrada',
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
