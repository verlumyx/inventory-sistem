<?php

namespace App\Inventory\Entry\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Entry\Handlers\ListEntriesHandler;
use App\Inventory\Entry\Requests\ListEntriesRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ListEntriesController extends Controller
{
    public function __construct(
        private ListEntriesHandler $listEntriesHandler
    ) {}

    /**
     * Display a listing of the entries.
     */
    public function __invoke(ListEntriesRequest $request): Response
    {
        $filters = $request->getFilters();
        $paginationParams = $request->getPaginationParams();
        
        $entries = $this->listEntriesHandler->handlePaginated(
            $filters,
            $paginationParams['per_page']
        );

        // Formatear datos para la vista
        $formattedEntries = $entries->through(function ($entry) {
            return [
                'id' => $entry->id,
                'code' => $entry->code,
                'name' => $entry->name,
                'description' => $entry->description,
                'status' => $entry->status,
                'status_text' => $entry->status_text,
                'created_at' => $entry->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $entry->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return Inertia::render('entries/Index', [
            'entries' => $formattedEntries,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'per_page' => $entries->perPage(),
                'total' => $entries->total(),
                'from' => $entries->firstItem(),
                'to' => $entries->lastItem(),
            ],
        ]);
    }

    /**
     * Get entries for API.
     */
    public function api(ListEntriesRequest $request): JsonResponse
    {
        $filters = $request->getFilters();
        $paginationParams = $request->getPaginationParams();
        
        $entries = $this->listEntriesHandler->handlePaginated(
            $filters,
            $paginationParams['per_page']
        );

        // Formatear datos para API
        $formattedEntries = $entries->through(function ($entry) {
            return $entry->toApiArray();
        });

        return response()->json([
            'data' => $formattedEntries,
            'meta' => [
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'per_page' => $entries->perPage(),
                'total' => $entries->total(),
                'from' => $entries->firstItem(),
                'to' => $entries->lastItem(),
            ],
        ]);
    }

    /**
     * Search entries.
     */
    public function search(ListEntriesRequest $request): JsonResponse
    {
        if (!$request->isSearching()) {
            return response()->json([
                'data' => [],
                'message' => 'No search term provided',
            ], 400);
        }

        $paginationParams = $request->getPaginationParams();
        
        $entries = $this->listEntriesHandler->handleSearch(
            $request->get('search'),
            $paginationParams['per_page']
        );

        // Formatear datos para API
        $formattedEntries = $entries->through(function ($entry) {
            return $entry->toApiArray();
        });

        return response()->json([
            'data' => $formattedEntries,
            'meta' => [
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'per_page' => $entries->perPage(),
                'total' => $entries->total(),
                'from' => $entries->firstItem(),
                'to' => $entries->lastItem(),
            ],
            'search_term' => $request->get('search'),
        ]);
    }

    /**
     * Get active entries.
     */
    public function active(): JsonResponse
    {
        $entries = $this->listEntriesHandler->handleActive();

        return response()->json([
            'data' => $entries->map(function ($entry) {
                return $entry->toApiArray();
            }),
        ]);
    }

    /**
     * Get inactive entries.
     */
    public function inactive(): JsonResponse
    {
        $entries = $this->listEntriesHandler->handleInactive();

        return response()->json([
            'data' => $entries->map(function ($entry) {
                return $entry->toApiArray();
            }),
        ]);
    }

    /**
     * Get latest entries.
     */
    public function latest(): JsonResponse
    {
        $entries = $this->listEntriesHandler->handleLatest(10);

        return response()->json([
            'data' => $entries->map(function ($entry) {
                return $entry->toApiArray();
            }),
        ]);
    }

    /**
     * Get entries statistics.
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->listEntriesHandler->handleStatistics();

        return response()->json([
            'data' => $statistics,
        ]);
    }
}
