<?php

namespace App\Inventory\Item\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Inventory\Item\Contracts\ItemRepositoryInterface;
use App\Inventory\Item\Handlers\CreateItemHandler;
use App\Inventory\Item\Handlers\ExportTemplateHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportExportController extends Controller
{
    public function __construct(
        private readonly ItemRepositoryInterface $repository,
        private readonly CreateItemHandler $createHandler,
        private readonly ExportTemplateHandler $exportTemplateHandler
    ) {}

    /**
     * Descargar plantilla de importación de items (archivo estático)
     */
    public function downloadTemplate()
    {
        try {
            // Ruta del archivo de plantilla estático
            $templatePath = public_path('templates/plantilla_articulos.xlsx');

            // Verificar que el archivo existe
            if (!file_exists($templatePath)) {
                throw new \Exception('El archivo de plantilla no existe: ' . $templatePath);
            }

            // Verificar que el archivo no está vacío
            if (filesize($templatePath) === 0) {
                throw new \Exception('El archivo de plantilla está vacío');
            }

            Log::info('Descargando plantilla estática', [
                'file_path' => $templatePath,
                'file_size' => filesize($templatePath)
            ]);

            return response()->download(
                $templatePath,
                'plantilla_articulos.xlsx.xlsx',
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="plantilla_articulos.xlsx"',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0'
                ]
            );

        } catch (\Exception $e) {
            Log::error('Error descargando plantilla estática', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Si es una petición AJAX, retornar JSON
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'error' => 'Error descargando plantilla: ' . $e->getMessage()
                ], 500);
            }

            // Si es una petición normal, redirigir con error
            return redirect()->back()->with('error', 'Error al descargar la plantilla: ' . $e->getMessage());
        }
    }

    /**
     * Importar items desde archivo CSV o Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);

        $imported = 0;
        $errors = 0;
        $errorMessages = [];

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            // Leer archivo según su tipo
            if (in_array($extension, ['csv'])) {
                // Leer archivo CSV
                $rows = [];
                $handle = fopen($file->getPathname(), 'r');

                // Saltar BOM si existe
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                while (($row = fgetcsv($handle)) !== false) {
                    $rows[] = $row;
                }
                fclose($handle);
            } else {
                // Leer archivo Excel
                $spreadsheet = IOFactory::load($file->getPathname());
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
            }

            // Verificar que tenga headers
            if (empty($rows) || count($rows) < 2) {
                throw new \Exception('El archivo debe contener al menos una fila de headers y una fila de datos');
            }

            // Procesar cada fila (saltando la primera que son los headers)
            DB::beginTransaction();

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Saltar filas vacías
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Validar campos obligatorios (sin código porque se autogenera)
                    $name = trim($row[0] ?? '');
                    $price = $row[2] ?? null;

                    if (empty($name) || $price === null || $price === '') {
                        $errorMessages[] = "Fila " . ($i + 1) . ": Nombre y Precio son obligatorios";
                        $errors++;
                        continue;
                    }

                    // Preparar datos
                    $description = trim($row[1] ?? '');
                    $unit = trim($row[3] ?? 'pcs');
                    $qrCode = trim($row[4] ?? '');
                    $status = trim($row[5] ?? 'Activo');

                    // Validar precio
                    if (!is_numeric($price) || $price < 0) {
                        $errorMessages[] = "Fila " . ($i + 1) . ": El precio debe ser un número válido mayor o igual a 0";
                        $errors++;
                        continue;
                    }

                    // Validar estado
                    $isActive = strtolower($status) === 'activo';

                    // Verificar código de barra único si se proporciona
                    if (!empty($qrCode) && !$this->repository->isQrCodeUnique($qrCode)) {
                        $errorMessages[] = "Fila " . ($i + 1) . ": El código de barra '{$qrCode}' ya existe";
                        $errors++;
                        continue;
                    }

                    // Preparar datos para crear item (sin código porque se autogenera)
                    $itemData = [
                        'name' => $name,
                        'description' => $description ?: null,
                        'price' => (float) $price,
                        'unit' => $unit,
                        'qr_code' => $qrCode ?: null,
                        'status' => $isActive
                    ];

                    // Crear el item usando el handler
                    $this->createHandler->handle($itemData);
                    $imported++;

                } catch (\Exception $e) {
                    $errorMessages[] = "Fila " . ($i + 1) . ": " . $e->getMessage();
                    $errors++;
                    Log::error("Error importing item at row " . ($i + 1), [
                        'error' => $e->getMessage(),
                        'row_data' => $row
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('items.index')
                ->with('success', "Importación completada: {$imported} artículos importados, {$errors} errores.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('items.index')
                ->with('error', 'Error al importar archivo: ' . $e->getMessage());
        }
    }
}
