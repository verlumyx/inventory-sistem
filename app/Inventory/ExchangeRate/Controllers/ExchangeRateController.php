<?php

namespace App\Inventory\ExchangeRate\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\ExchangeRate\Handlers\GetExchangeRateHandler;
use App\Inventory\ExchangeRate\Handlers\UpdateExchangeRateHandler;
use App\Inventory\ExchangeRate\Requests\UpdateExchangeRateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class ExchangeRateController extends Controller
{
    public function __construct(
        private GetExchangeRateHandler $getExchangeRateHandler,
        private UpdateExchangeRateHandler $updateExchangeRateHandler
    ) {}

    /**
     * Display the exchange rate configuration page.
     */
    public function index(): Response
    {
        try {
            // Check if table exists first
            if (!\Schema::hasTable('exchange_rates')) {
                return Inertia::render('settings/exchange-rate', [
                    'exchangeRate' => [
                        'id' => null,
                        'rate' => 1.0000,
                        'formatted_rate' => '1.0000',
                        'created_at' => null,
                        'updated_at' => null,
                    ],
                ])->with('error', 'La tabla de tasa de cambio no existe. Por favor, ejecuta las migraciones.');
            }

            $exchangeRate = $this->getExchangeRateHandler->handleForDisplay();

            return Inertia::render('settings/exchange-rate', [
                'exchangeRate' => $exchangeRate,
            ]);

        } catch (\Exception $e) {
            return Inertia::render('settings/exchange-rate', [
                'exchangeRate' => [
                    'id' => null,
                    'rate' => 1.0000,
                    'formatted_rate' => '1.0000',
                    'created_at' => null,
                    'updated_at' => null,
                ],
            ])->with('error', 'Error al cargar la configuración de tasa de cambio: ' . $e->getMessage());
        }
    }

    /**
     * Update the exchange rate.
     */
    public function update(UpdateExchangeRateRequest $request): RedirectResponse
    {
        try {
            $rate = $request->getValidatedRate();
            
            $exchangeRate = $this->updateExchangeRateHandler->handle($rate);

            return redirect()
                ->route('settings.exchange-rate')
                ->with('success', 'Tasa de cambio actualizada exitosamente.');

        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar la tasa de cambio. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Get current exchange rate (API endpoint).
     */
    public function current()
    {
        try {
            $rate = $this->getExchangeRateHandler->getCurrentRate();

            return response()->json([
                'success' => true,
                'data' => [
                    'rate' => $rate,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la tasa de cambio.',
            ], 500);
        }
    }
}
