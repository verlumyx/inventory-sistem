<?php

namespace App\Http\Controllers;

use App\Inventory\Entry\Contracts\EntryRepositoryInterface;
use App\Inventory\Invoice\Contracts\InvoiceRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private EntryRepositoryInterface $entryRepository,
        private InvoiceRepositoryInterface $invoiceRepository
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

        return [
            'stats' => [
                'recent_entries' => $recentEntries,
                'pending_invoices' => $pendingInvoices,
                'paid_invoices' => $paidInvoices,
            ],
            'chart_data' => $monthlyData
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
}
