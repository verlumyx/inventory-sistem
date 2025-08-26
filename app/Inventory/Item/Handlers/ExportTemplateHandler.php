<?php

namespace App\Inventory\Item\Handlers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExportTemplateHandler
{
    /**
     * Generar plantilla de importación de items
     */
    public function generateTemplate(): string
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

        // Configurar título de la hoja
        $sheet->setTitle('Plantilla Artículos');

        // Headers de las columnas
        $headers = [
            'A1' => 'Código',
            'B1' => 'Nombre',
            'C1' => 'Descripción',
            'D1' => 'Precio',
            'E1' => 'Unidad',
            'F1' => 'Código de Barra',
            'G1' => 'Estado'
        ];

        // Establecer headers
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Estilo para los headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];

        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Ajustar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(15); // Código
        $sheet->getColumnDimension('B')->setWidth(30); // Nombre
        $sheet->getColumnDimension('C')->setWidth(40); // Descripción
        $sheet->getColumnDimension('D')->setWidth(12); // Precio
        $sheet->getColumnDimension('E')->setWidth(10); // Unidad
        $sheet->getColumnDimension('F')->setWidth(20); // Código de Barra
        $sheet->getColumnDimension('G')->setWidth(12); // Estado

        // Agregar ejemplos de datos
        $examples = [
            ['IT-001', 'Laptop Dell Inspiron', 'Laptop para oficina con 8GB RAM', '850.00', 'pcs', '1234567890123', 'Activo'],
            ['IT-002', 'Mouse Inalámbrico', 'Mouse inalámbrico ergonómico', '25.50', 'pcs', '1234567890124', 'Activo'],
            ['IT-003', 'Teclado Mecánico', 'Teclado mecánico RGB', '120.00', 'pcs', '1234567890125', 'Activo']
        ];

        $row = 2;
        foreach ($examples as $example) {
            $col = 'A';
            foreach ($example as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        // Agregar instrucciones en una hoja separada
        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instrucciones');

        $instructions = [
            'INSTRUCCIONES PARA IMPORTAR ARTÍCULOS',
            '',
            '1. Complete la información en la hoja "Plantilla Artículos"',
            '2. Campos obligatorios: Código, Nombre, Precio',
            '3. Campos opcionales: Descripción, Unidad, Código de Barra, Estado',
            '',
            'FORMATO DE CAMPOS:',
            '• Código: Texto único (ej: IT-001)',
            '• Nombre: Texto descriptivo del artículo',
            '• Descripción: Texto opcional con detalles',
            '• Precio: Número decimal (ej: 850.00)',
            '• Unidad: Texto (ej: pcs, kg, m, etc.)',
            '• Código de Barra: Número o texto',
            '• Estado: "Activo" o "Inactivo"',
            '',
            'NOTAS IMPORTANTES:',
            '• No modifique los headers de las columnas',
            '• Elimine las filas de ejemplo antes de importar',
            '• Asegúrese de que los códigos sean únicos',
            '• El precio debe ser un número válido',
            '• Si no especifica estado, se asignará "Activo" por defecto'
        ];

        $row = 1;
        foreach ($instructions as $instruction) {
            $instructionsSheet->setCellValue('A' . $row, $instruction);
            if ($row === 1) {
                // Título en negrita
                $instructionsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            }
            $row++;
        }

        $instructionsSheet->getColumnDimension('A')->setWidth(60);

            // Guardar archivo temporal
            $tempFile = tempnam(sys_get_temp_dir(), 'items_template_') . '.xlsx';

            // Verificar que el directorio temporal sea escribible
            if (!is_writable(dirname($tempFile))) {
                throw new \Exception('El directorio temporal no es escribible: ' . dirname($tempFile));
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            // Verificar que el archivo se creó correctamente
            if (!file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception('No se pudo crear el archivo Excel');
            }

            return $tempFile;

        } catch (\Exception $e) {
            // Log del error específico
            \Log::error('Error en ExportTemplateHandler::generateTemplate', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'temp_dir' => sys_get_temp_dir(),
                'temp_dir_writable' => is_writable(sys_get_temp_dir()),
                'extensions' => [
                    'xmlwriter' => extension_loaded('xmlwriter'),
                    'zip' => extension_loaded('zip'),
                    'gd' => extension_loaded('gd'),
                ]
            ]);

            throw $e;
        }
    }
}
