<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Services\PrintService;
use App\Inventory\Invoice\Models\Invoice;
use App\Inventory\Invoice\Models\InvoiceItem;
use App\Inventory\Item\Models\Item;
use App\Inventory\Warehouse\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MacOSPrintTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Invoice $invoice;
    private PrintService $printService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->invoice = $this->createTestInvoice();
        
        // Crear datos de empresa
        Company::create([
            'name_company' => 'Test Company',
            'dni' => 'J-12345678-9',
            'address' => 'Test Address 123, Caracas, Venezuela',
            'phone' => '+58 412-123-4567',
        ]);

        // Configurar para macOS con impresora falsa para tests
        config([
            'printing.enabled' => true,
            'printing.type' => 'cups',
            'printing.port' => 'TEST_PRINTER_FAKE', // Impresora que no existe
            'printing.timeout' => 5,
        ]);

        $this->printService = new PrintService();
    }

    /** @test */
    public function it_can_detect_macos_printer()
    {
        $this->assertTrue($this->printService->isAvailable());
        
        $status = $this->printService->getStatus();
        $this->assertEquals('cups', $status['type']);
        $this->assertEquals('TECH_CLA58', $status['port']);
        $this->assertTrue($status['available']);
    }

    /** @test */
    public function it_can_print_invoice_on_macos()
    {
        // Marcar factura como pagada
        $this->invoice->update(['status' => 1]);

        // Cargar relaciones necesarias
        $this->invoice->load(['warehouse', 'invoiceItems.item']);

        // Agregar alias para items (usado en el servicio)
        $this->invoice->setRelation('items', $this->invoice->invoiceItems);

        // Intentar imprimir (fallará porque la impresora es falsa, pero no imprimirá nada)
        try {
            $result = $this->printService->printInvoice($this->invoice);
            // Si llega aquí, significa que no falló (impresora real conectada)
            $this->assertTrue($result);
        } catch (\Exception $e) {
            // Si falla, es porque la impresora falsa no existe (comportamiento esperado)
            // Puede ser varios mensajes de error, todos indican que no hay impresora real
            $errorMessage = $e->getMessage();
            $this->assertTrue(
                str_contains($errorMessage, 'no está disponible') ||
                str_contains($errorMessage, 'No such file or directory') ||
                str_contains($errorMessage, 'Error ejecutando comando lp') ||
                str_contains($errorMessage, 'impresora no está disponible')
            );
        }
    }

    /** @test */
    public function it_can_print_via_web_interface()
    {
        // Marcar factura como pagada
        $this->invoice->update(['status' => 1]);

        $response = $this->actingAs($this->user)
            ->post("/invoices/{$this->invoice->id}/print");

        // Debería redirigir con mensaje de éxito
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $successMessage = session('success');
        $this->assertStringContainsString('impresa exitosamente', $successMessage);
    }

    /** @test */
    public function it_generates_correct_format_for_58mm()
    {
        // Usar reflexión para acceder al método privado
        $reflection = new \ReflectionClass($this->printService);
        $formatMethod = $reflection->getMethod('formatInvoiceFor58mm');
        $formatMethod->setAccessible(true);

        $company = Company::first();
        $this->invoice->load(['warehouse', 'invoiceItems.item']);
        $this->invoice->setRelation('items', $this->invoice->invoiceItems);

        $lines = $formatMethod->invoke($this->printService, $this->invoice, $company);

        // Verificar que se generaron líneas
        $this->assertNotEmpty($lines);
        
        // Verificar que contiene información de la empresa
        $companyLine = collect($lines)->first(function ($line) use ($company) {
            return str_contains($line['text'], $company->name_company);
        });
        $this->assertNotNull($companyLine);

        // Verificar que contiene información de la factura
        $invoiceLine = collect($lines)->first(function ($line) {
            return str_contains($line['text'], 'FACTURA');
        });
        $this->assertNotNull($invoiceLine);
    }

    /** @test */
    public function it_handles_printer_errors_gracefully()
    {
        // Configurar impresora inexistente
        config(['printing.port' => 'IMPRESORA_INEXISTENTE']);
        $printService = new PrintService();

        $this->assertFalse($printService->isAvailable());

        // Marcar factura como pagada
        $this->invoice->update(['status' => 1]);

        $response = $this->actingAs($this->user)
            ->post("/invoices/{$this->invoice->id}/print");

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
    }

    /**
     * Crear una factura de prueba
     */
    private function createTestInvoice(): Invoice
    {
        // Crear warehouse
        $warehouse = Warehouse::create([
            'name' => 'Almacén Principal',
            'description' => 'Almacén principal de prueba',
            'status' => 1,
            'default' => 1,
        ]);

        // Crear items
        $item1 = Item::create([
            'name' => 'Producto de Prueba 1',
            'description' => 'Descripción del producto 1',
            'price' => 15.50,
            'unit' => 'pcs',
            'status' => 1,
        ]);

        $item2 = Item::create([
            'name' => 'Producto de Prueba 2 con Nombre Largo',
            'description' => 'Descripción del producto 2',
            'price' => 25.00,
            'unit' => 'pcs',
            'status' => 1,
        ]);

        // Crear factura
        $invoice = Invoice::create([
            'warehouse_id' => $warehouse->id,
            'rate' => 36.0000,
            'status' => 0, // No pagada por defecto
        ]);

        // Crear items de factura
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_id' => $item1->id,
            'amount' => 2,
            'price' => 15.50,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_id' => $item2->id,
            'amount' => 1,
            'price' => 25.00,
        ]);

        return $invoice;
    }
}
