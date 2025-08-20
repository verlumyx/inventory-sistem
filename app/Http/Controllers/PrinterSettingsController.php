<?php

namespace App\Http\Controllers;

use App\Models\PrinterSettings;
use App\Services\PrintService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class PrinterSettingsController extends Controller
{
    /**
     * Display printer settings page.
     */
    public function index(): Response
    {
        // Crear configuración predeterminada si no existe
        $settings = PrinterSettings::createDefaultIfNotExists();

        return Inertia::render('settings/printer', [
            'settings' => $settings->toApiArray(),
            'availableTypes' => PrinterSettings::getAvailableTypes(),
            'parityOptions' => PrinterSettings::getParityOptions(),
            'flowControlOptions' => PrinterSettings::getFlowControlOptions(),
            'logLevels' => PrinterSettings::getLogLevels(),
        ]);
    }

    /**
     * Update printer settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'enabled' => 'boolean',
            'type' => 'required|in:usb,serial,network,cups,macos',
            'port' => 'nullable|string|max:255',
            'printer_name' => 'nullable|string|max:255',
            'timeout' => 'required|integer|min:1|max:60',
            'network_host' => 'nullable|string|max:255',
            'network_port' => 'nullable|integer|min:1|max:65535',
            'network_timeout' => 'nullable|integer|min:1|max:120',
            'baud_rate' => 'nullable|integer|in:9600,19200,38400,57600,115200',
            'data_bits' => 'nullable|integer|in:7,8',
            'stop_bits' => 'nullable|integer|in:1,2',
            'parity' => 'nullable|in:none,odd,even',
            'flow_control' => 'nullable|in:none,rts/cts,xon/xoff',
            'paper_width' => 'required|integer|min:20|max:80',
            'paper_margin' => 'nullable|integer|min:0|max:10',
            'line_spacing' => 'nullable|integer|min:1|max:5',
            'retry_enabled' => 'boolean',
            'retry_attempts' => 'nullable|integer|min:1|max:10',
            'retry_delay' => 'nullable|integer|min:1|max:30',
            'log_enabled' => 'boolean',
            'log_level' => 'nullable|in:debug,info,warning,error',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $settings = PrinterSettings::getDefault();

            if (!$settings) {
                $settings = PrinterSettings::createDefaultIfNotExists();
            }

            $settings->update([
                'name' => $request->input('name'),
                'enabled' => $request->boolean('enabled'),
                'type' => $request->input('type'),
                'port' => $request->input('port'),
                'printer_name' => $request->input('printer_name'),
                'timeout' => $request->input('timeout'),
                'network_host' => $request->input('network_host'),
                'network_port' => $request->input('network_port', 9100),
                'network_timeout' => $request->input('network_timeout', 10),
                'baud_rate' => $request->input('baud_rate', 9600),
                'data_bits' => $request->input('data_bits', 8),
                'stop_bits' => $request->input('stop_bits', 1),
                'parity' => $request->input('parity', 'none'),
                'flow_control' => $request->input('flow_control', 'none'),
                'paper_width' => $request->input('paper_width'),
                'paper_margin' => $request->input('paper_margin', 0),
                'line_spacing' => $request->input('line_spacing', 1),
                'retry_enabled' => $request->boolean('retry_enabled'),
                'retry_attempts' => $request->input('retry_attempts', 3),
                'retry_delay' => $request->input('retry_delay', 1),
                'log_enabled' => $request->boolean('log_enabled'),
                'log_level' => $request->input('log_level', 'info'),
            ]);

            Log::info('Configuración de impresora actualizada', [
                'settings_id' => $settings->id,
                'user_id' => auth()->id(),
                'changes' => $settings->getChanges()
            ]);

            return redirect()
                ->back()
                ->with('success', 'Configuración de impresora actualizada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error actualizando configuración de impresora', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'Error al actualizar la configuración: ' . $e->getMessage()])
                ->withInput();
        }
    }
    /**
     * Test printer connection.
     */
    public function testConnection(): RedirectResponse
    {
        try {
            $settings = PrinterSettings::getDefault();

            if (!$settings) {
                return redirect()
                    ->back()
                    ->withErrors(['error' => 'No hay configuración de impresora disponible.']);
            }

            // Crear servicio de impresión con configuración actual
            $printService = new PrintService($settings->toPrintConfig());

            // Verificar disponibilidad
            if (!$printService->isAvailable()) {
                return redirect()
                    ->back()
                    ->withErrors(['error' => 'La impresora no está disponible con la configuración actual.']);
            }

            // Generar y enviar prueba de impresión
            $testData = $this->generateTestPrint();

            // Usar el método público del servicio
            $result = $printService->sendTestData($testData);

            if ($result) {
                Log::info('Prueba de impresión exitosa', [
                    'settings_id' => $settings->id,
                    'user_id' => auth()->id()
                ]);

                return redirect()
                    ->back()
                    ->with('success', 'Prueba de impresión exitosa. Verifique que se haya impreso el ticket de prueba.');
            } else {
                return redirect()
                    ->back()
                    ->withErrors(['error' => 'La prueba de impresión falló. Verifique la configuración.']);
            }

        } catch (\Exception $e) {
            Log::error('Error en prueba de impresión', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'Error en la prueba: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate test print data.
     */
    private function generateTestPrint(): string
    {
        $lines = [];
        $lines[] = str_repeat('=', 32);
        $lines[] = $this->centerText('PRUEBA DE IMPRESIÓN');
        $lines[] = str_repeat('=', 32);
        $lines[] = '';
        $lines[] = 'Fecha: ' . now()->format('d/m/Y H:i:s');
        $lines[] = 'Sistema: Inventario Desktop';
        $lines[] = '';
        $lines[] = str_repeat('-', 32);
        $lines[] = 'Esta es una prueba de impresión';
        $lines[] = 'para verificar la configuración';
        $lines[] = 'de la impresora térmica.';
        $lines[] = str_repeat('-', 32);
        $lines[] = '';
        $lines[] = $this->centerText('Prueba exitosa');
        $lines[] = '';
        $lines[] = '';
        $lines[] = '';

        return implode("\n", $lines);
    }

    /**
     * Center text for 32 character width.
     */
    private function centerText(string $text): string
    {
        $width = 32;
        $length = strlen($text);

        if ($length >= $width) {
            return substr($text, 0, $width);
        }

        $padding = ($width - $length) / 2;
        $leftPadding = floor($padding);
        $rightPadding = $width - $length - $leftPadding;

        return str_repeat(' ', $leftPadding) . $text . str_repeat(' ', $rightPadding);
    }

    /**
     * Get available printers (for CUPS systems).
     */
    public function getAvailablePrinters(): \Illuminate\Http\JsonResponse
    {
        try {
            $printers = [];

            // Ejecutar comando lpstat para obtener impresoras disponibles
            $output = [];
            $returnCode = 0;
            exec('lpstat -p 2>/dev/null', $output, $returnCode);

            if ($returnCode === 0) {
                foreach ($output as $line) {
                    if (preg_match('/printer (\S+)/', $line, $matches)) {
                        $printers[] = [
                            'name' => $matches[1],
                            'description' => trim(str_replace('printer ' . $matches[1], '', $line))
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'printers' => $printers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo impresoras: ' . $e->getMessage(),
                'printers' => []
            ]);
        }
    }

}