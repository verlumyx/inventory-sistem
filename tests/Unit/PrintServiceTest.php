<?php

namespace Tests\Unit;

use App\Services\PrintService;
use App\Models\Company;
use App\Inventory\Invoice\Models\Invoice;
use App\Inventory\Invoice\Models\InvoiceItem;
use App\Inventory\Item\Models\Item;
use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class PrintServiceTest extends TestCase
{
    use RefreshDatabase;

    private PrintService $printService;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurar valores por defecto para los tests
        config([
            'printing.enabled' => false,
            'printing.port' => '/dev/test',
            'printing.type' => 'usb',
            'printing.timeout' => 5,
        ]);

        $this->printService = new PrintService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_check_if_printing_is_available()
    {
        // Configurar para que la impresión esté deshabilitada
        config(['printing.enabled' => false]);
        
        $this->assertFalse($this->printService->isAvailable());
    }

    /** @test */
    public function it_can_get_printer_status()
    {
        // Recrear el servicio con nueva configuración
        config([
            'printing.enabled' => true,
            'printing.port' => '/dev/test',
            'printing.type' => 'usb'
        ]);
        $this->printService = new PrintService();

        $status = $this->printService->getStatus();

        $this->assertIsArray($status);
        $this->assertArrayHasKey('enabled', $status);
        $this->assertArrayHasKey('port', $status);
        $this->assertArrayHasKey('type', $status);
        $this->assertArrayHasKey('available', $status);
        $this->assertTrue($status['enabled']);
        $this->assertEquals('/dev/test', $status['port']);
        $this->assertEquals('usb', $status['type']);
        // No verificamos available porque el puerto no existe en el test
    }

    /** @test */
    public function it_throws_exception_when_printing_is_disabled()
    {
        config(['printing.enabled' => false]);

        $invoice = $this->createTestInvoice();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('La impresión no está habilitada en la configuración');

        $this->printService->printInvoice($invoice);
    }

    /** @test */
    public function it_throws_exception_when_invoice_is_not_paid()
    {
        // Recrear el servicio con nueva configuración
        config(['printing.enabled' => true]);
        $this->printService = new PrintService();

        $invoice = $this->createTestInvoice();
        $invoice->status = 0; // No pagada
        $invoice->save();

        // Crear datos de empresa para que no falle por eso
        $this->createTestCompany();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Solo se pueden imprimir facturas pagadas');

        $this->printService->printInvoice($invoice);
    }

    /** @test */
    public function it_throws_exception_when_company_data_is_missing()
    {
        // Recrear el servicio con nueva configuración
        config(['printing.enabled' => true]);
        $this->printService = new PrintService();

        $invoice = $this->createTestInvoice();
        $invoice->status = 1; // Pagada
        $invoice->save();

        // Asegurar que no hay datos de empresa
        Company::query()->delete();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No se han configurado los datos de la empresa');

        $this->printService->printInvoice($invoice);
    }

    /** @test */
    public function it_can_format_text_for_58mm_paper()
    {
        $reflection = new \ReflectionClass($this->printService);
        
        // Test centerText method
        $centerMethod = $reflection->getMethod('centerText');
        $centerMethod->setAccessible(true);
        
        $centered = $centerMethod->invoke($this->printService, 'TEST');
        $this->assertEquals(32, strlen($centered));
        $this->assertStringContainsString('TEST', $centered);

        // Test rightAlign method
        $rightMethod = $reflection->getMethod('rightAlign');
        $rightMethod->setAccessible(true);
        
        $rightAligned = $rightMethod->invoke($this->printService, 'TOTAL');
        $this->assertEquals(32, strlen($rightAligned));
        $this->assertStringEndsWith('TOTAL', $rightAligned);

        // Test repeatChar method
        $repeatMethod = $reflection->getMethod('repeatChar');
        $repeatMethod->setAccessible(true);
        
        $repeated = $repeatMethod->invoke($this->printService, '=', 10);
        $this->assertEquals('==========', $repeated);
    }

    /** @test */
    public function it_can_wrap_long_text()
    {
        $reflection = new \ReflectionClass($this->printService);
        $wrapMethod = $reflection->getMethod('wrapText');
        $wrapMethod->setAccessible(true);
        
        $longText = 'Este es un texto muy largo que debería ser dividido en múltiples líneas para caber en el papel de 58mm';
        $wrapped = $wrapMethod->invoke($this->printService, $longText);
        
        $lines = explode("\n", $wrapped);
        foreach ($lines as $line) {
            $this->assertLessThanOrEqual(32, strlen($line));
        }
    }

    /** @test */
    public function it_can_generate_escpos_commands()
    {
        $reflection = new \ReflectionClass($this->printService);
        $generateMethod = $reflection->getMethod('generateEscPosCommands');
        $generateMethod->setAccessible(true);
        
        $lines = [
            ['text' => 'Test Line', 'style' => 'normal'],
            ['text' => 'Bold Line', 'style' => 'bold'],
        ];
        
        $commands = $generateMethod->invoke($this->printService, $lines);
        
        $this->assertIsString($commands);
        $this->assertStringContainsString('Test Line', $commands);
        $this->assertStringContainsString('Bold Line', $commands);
        // Verificar que contiene comandos ESC/POS
        $this->assertStringContainsString("\x1B\x40", $commands); // INIT
        $this->assertStringContainsString("\x1D\x56\x00", $commands); // CUT
    }

    /**
     * Crear una factura de prueba
     */
    private function createTestInvoice(): Invoice
    {
        // Crear warehouse
        $warehouse = Warehouse::create([
            'name' => 'Test Warehouse',
            'description' => 'Test Description',
            'status' => 1,
            'default' => 1,
        ]);

        // Crear item
        $item = Item::create([
            'name' => 'Test Item',
            'description' => 'Test Item Description',
            'price' => 10.00,
            'unit' => 'pcs',
            'status' => 1,
        ]);

        // Crear factura
        $invoice = Invoice::create([
            'warehouse_id' => $warehouse->id,
            'rate' => 1.0000,
            'status' => 1, // Pagada
        ]);

        // Crear item de factura
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_id' => $item->id,
            'amount' => 2,
            'price' => 10.00,
        ]);

        // Cargar relaciones
        $invoice->load(['warehouse', 'invoiceItems.item']);

        // Agregar alias para items (usado en el servicio)
        $invoice->setRelation('items', $invoice->invoiceItems);

        return $invoice;
    }

    /**
     * Crear datos de empresa de prueba
     */
    private function createTestCompany(): Company
    {
        return Company::create([
            'name_company' => 'Test Company',
            'dni' => 'J-12345678-9',
            'address' => 'Test Address 123',
            'phone' => '+58 412-123-4567',
        ]);
    }

    /** @test */
    public function it_can_normalize_text_with_accents_and_special_characters()
    {
        // Test basic accents
        $this->assertEquals('aeiou', $this->printService->normalizeText('áéíóú'));
        $this->assertEquals('AEIOU', $this->printService->normalizeText('ÁÉÍÓÚ'));

        // Test Spanish characters
        $this->assertEquals('nino', $this->printService->normalizeText('niño'));
        $this->assertEquals('NINO', $this->printService->normalizeText('NIÑO'));

        // Test special punctuation
        $this->assertEquals('Gracias por su compra!', $this->printService->normalizeText('¡Gracias por su compra!'));
        $this->assertEquals('Que tal?', $this->printService->normalizeText('¿Qué tal?'));

        // Test quotes and dashes
        $this->assertEquals('"Texto"', $this->printService->normalizeText('"Texto"'));
        $this->assertEquals('Texto-separado', $this->printService->normalizeText('Texto–separado'));

        // Test real world examples
        $this->assertEquals('Panaderia "El Buen Sabor"', $this->printService->normalizeText('Panadería "El Buen Sabor"'));
        $this->assertEquals('Almacen Principal', $this->printService->normalizeText('Almacén Principal'));
        $this->assertEquals('Descripcion del producto', $this->printService->normalizeText('Descripción del producto'));
    }

    /** @test */
    public function it_shows_prices_in_bolivars_when_invoice_has_exchange_rate()
    {
        // Crear company de prueba
        $company = $this->createTestCompany();

        // Crear factura con tasa de cambio
        $invoice = Invoice::factory()->create([
            'rate' => 36.5000, // Tasa de ejemplo
        ]);

        // Crear item con precio en dólares
        $item = Item::factory()->create([
            'name' => 'Producto de prueba',
            'price' => 10.00, // $10.00
        ]);

        // Crear invoice item manualmente
        $invoiceItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_id' => $item->id,
            'amount' => 2.00,
            'price' => 10.00, // $10.00
        ]);

        $invoice->load(['warehouse', 'invoiceItems.item']);

        // Generar líneas de impresión usando reflexión para acceder al método privado
        $reflection = new \ReflectionClass($this->printService);
        $method = $reflection->getMethod('formatInvoiceFor58mm');
        $method->setAccessible(true);
        $lines = $method->invoke($this->printService, $invoice, $company);

        // Buscar la línea del item
        $itemLines = array_filter($lines, function($line) {
            return strpos($line['text'], 'Bs ') !== false;
        });

        $this->assertNotEmpty($itemLines, 'Debería haber líneas con precios en bolívares');

        // Verificar que el precio esté en bolívares (10.00 * 36.5000 = 365.00)
        $itemLine = array_values($itemLines)[0];
        $this->assertStringContainsString('Bs 365.00', $itemLine['text']);
        $this->assertStringContainsString('Bs 730.00', $itemLine['text']); // Subtotal: 2 x 365.00 = 730.00
    }

    /** @test */
    public function it_shows_prices_in_dollars_when_invoice_has_no_exchange_rate()
    {
        // Crear company de prueba
        $company = $this->createTestCompany();

        // Crear factura sin tasa de cambio (rate = 1.0000)
        $invoice = Invoice::factory()->create([
            'rate' => 1.0000,
        ]);

        // Crear item
        $item = Item::factory()->create([
            'name' => 'Producto de prueba',
            'price' => 10.00,
        ]);

        // Crear invoice item manualmente
        $invoiceItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_id' => $item->id,
            'amount' => 2.00,
            'price' => 10.00,
        ]);

        $invoice->load(['warehouse', 'invoiceItems.item']);

        // Generar líneas de impresión usando reflexión para acceder al método privado
        $reflection = new \ReflectionClass($this->printService);
        $method = $reflection->getMethod('formatInvoiceFor58mm');
        $method->setAccessible(true);
        $lines = $method->invoke($this->printService, $invoice, $company);

        // Buscar la línea del item
        $itemLines = array_filter($lines, function($line) {
            return strpos($line['text'], '$') !== false && strpos($line['text'], 'x') !== false;
        });

        $this->assertNotEmpty($itemLines, 'Debería haber líneas con precios en dólares');

        // Verificar que el precio esté en dólares
        $itemLine = array_values($itemLines)[0];
        $this->assertStringContainsString('$10.00', $itemLine['text']);
        $this->assertStringContainsString('$20.00', $itemLine['text']); // Subtotal: 2 x 10.00 = 20.00
    }
}
