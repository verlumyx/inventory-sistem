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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportExportController extends Controller
{
    public function __construct(
        private readonly ItemRepositoryInterface $repository,
        private readonly CreateItemHandler $createHandler
    ) {}

    /**
     * Descargar plantilla de importación de items (CSV)
     */
    public function downloadTemplate()
    {
        // Crear contenido CSV (sin código porque se autogenera)
        $headers = ['Nombre', 'Descripción', 'Precio', 'Unidad', 'Código de Barra', 'Estado'];

        $examples = [
            ['Laptop Dell Inspiron', 'Laptop para oficina con 8GB RAM', '850.00', 'pcs', '1234567890123', 'Activo'],
            ['Mouse Inalámbrico', 'Mouse inalámbrico ergonómico', '25.50', 'pcs', '1234567890124', 'Activo'],
            ['Teclado Mecánico', 'Teclado mecánico RGB', '120.00', 'pcs', '1234567890125', 'Activo']
        ];

        // Crear archivo temporal
        $tempFile = tempnam(sys_get_temp_dir(), 'items_template_') . '.csv';
        $handle = fopen($tempFile, 'w');

        // Escribir BOM para UTF-8
        fwrite($handle, "\xEF\xBB\xBF");

        // Escribir headers
        fputcsv($handle, $headers);

        // Escribir ejemplos
        foreach ($examples as $example) {
            fputcsv($handle, $example);
        }

        fclose($handle);

        return response()->download(
            $tempFile,
            'plantilla_articulos.csv',
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="plantilla_articulos.csv"',
            ]
        )->deleteFileAfterSend();
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
