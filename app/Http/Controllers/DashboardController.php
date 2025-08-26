<?php

namespace App\Http\Controllers;

use App\Inventory\Entry\Contracts\EntryRepositoryInterface;
use App\Inventory\Invoice\Contracts\InvoiceRepositoryInterface;
use App\Inventory\Item\Contracts\ItemRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private EntryRepositoryInterface $entryRepository,
        private InvoiceRepositoryInterface $invoiceRepository,
        private ItemRepositoryInterface $itemRepository
    ) {}

    /**
     * Display the dashboard.
     */
    public function index(): Response
    {
        $dashboardData = $this->getDashboardData();

        return Inertia::render('dashboard', $dashboardData);
    }

    /**
     * Get dashboard statistics data.
     */
    private function getDashboardData(): array
    {
        // Fechas para los últimos 7 días
        $sevenDaysAgo = Carbon::now()->subDays(7);
        $now = Carbon::now();

        // 1. Entradas recientes (últimos 7 días)
        $recentEntries = $this->entryRepository->getAll([
            'date_from' => $sevenDaysAgo->format('Y-m-d'),
            'date_to' => $now->format('Y-m-d')
        ])->count();

        // 2. Facturas pendientes (últimos 7 días)
        $pendingInvoices = $this->invoiceRepository->getAll([
            'status' => 0, // Por pagar
            'date_from' => $sevenDaysAgo->format('Y-m-d'),
            'date_to' => $now->format('Y-m-d')
        ])->count();

        // 3. Facturas pagadas (últimos 7 días)
        $paidInvoices = $this->invoiceRepository->getAll([
            'status' => 1, // Pagadas
            'date_from' => $sevenDaysAgo->format('Y-m-d'),
            'date_to' => $now->format('Y-m-d')
        ])->count();

        // 4. Datos para la gráfica de montos por mes (últimos 12 meses)
        $monthlyData = $this->getMonthlyInvoiceAmounts();

        // 5. Items con menor disponibilidad (top 10)
        $lowStockItems = $this->getLowStockItems();

        return [
            'stats' => [
                'recent_entries' => $recentEntries,
                'pending_invoices' => $pendingInvoices,
                'paid_invoices' => $paidInvoices,
            ],
            'chart_data' => $monthlyData,
            'low_stock_items' => $lowStockItems
        ];
    }

    /**
     * Get monthly invoice amounts for the last 12 months.
     */
    private function getMonthlyInvoiceAmounts(): array
    {
        $months = [];
        $data = [];

        // Generar los últimos 12 meses en orden descendente (mes actual primero)
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthName = $date->locale('es')->format('M Y');

            $months[] = $monthName;

            // Obtener facturas pagadas del mes
            $monthlyInvoices = $this->invoiceRepository->getAll([
                'status' => 1, // Solo facturas pagadas
                'month' => $date->month,
                'year' => $date->year
            ]);

            $monthlyAmount = $monthlyInvoices->sum(function ($invoice) {
                return $invoice->total_amount;
            });

            $data[] = $monthlyAmount;
        }

        return [
            'labels' => $months,
            'data' => $data
        ];
    }

    /**
     * Get items with lowest stock availability (top 10).
     */
    private function getLowStockItems(): array
    {
        // Obtener items con su stock total ordenados por menor disponibilidad
        $items = \App\Inventory\Item\Models\Item::select([
                'items.id',
                'items.code',
                'items.name',
                'items.price',
                'items.unit'
            ])
            ->join('warehouse_items', 'items.id', '=', 'warehouse_items.item_id')
            ->selectRaw('SUM(warehouse_items.quantity_available) as total_stock')
            ->where('items.status', true) // Solo items activos
            ->groupBy('items.id', 'items.code', 'items.name', 'items.price', 'items.unit')
            ->orderBy('total_stock', 'asc') // Menor stock primero
            ->limit(10)
            ->get();

        return $items->map(function ($item) {
            return [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'price' => $item->price,
                'unit' => $item->unit,
                'total_stock' => (float) $item->total_stock,
                'stock_status' => $item->total_stock <= 0 ? 'Sin Stock' :
                                ($item->total_stock <= 5 ? 'Stock Bajo' : 'En Stock')
            ];
        })->toArray();
    }
}
