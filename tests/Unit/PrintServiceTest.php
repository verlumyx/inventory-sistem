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
}
