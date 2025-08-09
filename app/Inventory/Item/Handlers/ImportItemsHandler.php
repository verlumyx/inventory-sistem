<?php

namespace App\Inventory\Item\Handlers;

use App\Inventory\Item\Contracts\ItemRepositoryInterface;
use App\Inventory\Item\Handlers\CreateItemHandler;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportItemsHandler
{
    public function __construct(
        private readonly ItemRepositoryInterface $repository,
        private readonly CreateItemHandler $createHandler
    ) {}

    /**
     * Importar items desde archivo Excel
     */
    public function handle(UploadedFile $file): array
    {
        $imported = 0;
        $errors = 0;
        $errorMessages = [];

        try {
            // Cargar el archivo Excel
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Obtener todas las filas con datos
            $rows = $worksheet->toArray();
            
            // Verificar que tenga headers
            if (empty($rows) || count($rows) < 2) {
                throw new \Exception('El archivo debe contener al menos una fila de headers y una fila de datos');
            }

            // Verificar headers esperados
            $expectedHeaders = ['Código', 'Nombre', 'Descripción', 'Precio', 'Unidad', 'Código de Barra', 'Estado'];
            $actualHeaders = array_slice($rows[0], 0, 7);
            
            if ($actualHeaders !== $expectedHeaders) {
                throw new \Exception('Los headers del archivo no coinciden con la plantilla esperada');
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
                    // Validar campos obligatorios
                    $code = trim($row[0] ?? '');
                    $name = trim($row[1] ?? '');
                    $price = $row[3] ?? null;

                    if (empty($code) || empty($name) || $price === null || $price === '') {
                        $errorMessages[] = "Fila " . ($i + 1) . ": Código, Nombre y Precio son obligatorios";
                        $errors++;
                        continue;
                    }

                    // Verificar que el código sea único
                    if (!$this->repository->isCodeUnique($code)) {
                        $errorMessages[] = "Fila " . ($i + 1) . ": El código '{$code}' ya existe";
                        $errors++;
                        continue;
                    }

                    // Preparar datos
                    $description = trim($row[2] ?? '');
                    $unit = trim($row[4] ?? 'pcs');
                    $qrCode = trim($row[5] ?? '');
                    $status = trim($row[6] ?? 'Activo');

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

                    // Crear DTO
                    $dto = [
                        'code' => $code,
                        'name' => $name,
                        'description' => $description ?: null,
                        'price' => (float) $price,
                        'unit' => $unit,
                        'qr_code' => $qrCode ?: null,
                        'status' => $isActive
                    ];

                    // Crear el item
                    $this->createHandler->handle($dto);
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

            // Log del resultado
            Log::info("Items import completed", [
                'imported' => $imported,
                'errors' => $errors,
                'error_messages' => $errorMessages
            ]);

            return [
                'imported' => $imported,
                'errors' => $errors,
                'error_messages' => $errorMessages
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Items import failed", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
