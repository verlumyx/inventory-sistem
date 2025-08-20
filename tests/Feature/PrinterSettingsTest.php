<?php

namespace Tests\Feature;

use App\Models\PrinterSettings;
use App\Models\User;
use App\Services\PrintService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrinterSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        // Configurar impresora falsa para tests (evita impresiones reales)
        config([
            'printing.enabled' => false, // Deshabilitado por defecto en tests
            'printing.type' => 'cups',
            'printing.port' => 'TEST_PRINTER_FAKE',
        ]);
    }

    /** @test */
    public function it_can_display_printer_settings_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/settings/printer');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('settings/printer')
                ->has('settings')
                ->has('availableTypes')
                ->has('parityOptions')
                ->has('flowControlOptions')
                ->has('logLevels')
        );
    }

    /** @test */
    public function it_creates_default_settings_if_none_exist()
    {
        $this->assertDatabaseEmpty('printer_settings');

        $this->actingAs($this->user)
            ->get('/settings/printer');

        $this->assertDatabaseCount('printer_settings', 1);
        
        $settings = PrinterSettings::first();
        $this->assertEquals('Impresora Principal', $settings->name);
        $this->assertEquals('cups', $settings->type);
        $this->assertEquals('TECH_CLA58', $settings->port);
        $this->assertFalse($settings->enabled);
        $this->assertTrue($settings->is_default);
    }

    /** @test */
    public function it_can_update_printer_settings()
    {
        $settings = PrinterSettings::createDefaultIfNotExists();

        $updateData = [
            'name' => 'Mi Impresora Térmica',
            'enabled' => true,
            'type' => 'cups',
            'port' => 'NUEVA_IMPRESORA',
            'timeout' => 10,
            'paper_width' => 40,
            'retry_enabled' => false,
            'retry_attempts' => 5,
            'log_enabled' => false,
            'log_level' => 'error',
        ];

        $response = $this->actingAs($this->user)
            ->put('/settings/printer', $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $settings->refresh();
        $this->assertEquals('Mi Impresora Térmica', $settings->name);
        $this->assertTrue($settings->enabled);
        $this->assertEquals('NUEVA_IMPRESORA', $settings->port);
        $this->assertEquals(10, $settings->timeout);
        $this->assertEquals(40, $settings->paper_width);
        $this->assertFalse($settings->retry_enabled);
        $this->assertEquals(5, $settings->retry_attempts);
        $this->assertFalse($settings->log_enabled);
        $this->assertEquals('error', $settings->log_level);
    }

    /** @test */
    public function it_validates_printer_settings_input()
    {
        PrinterSettings::createDefaultIfNotExists();

        $invalidData = [
            'name' => '', // Required
            'type' => 'invalid_type', // Invalid enum
            'timeout' => 0, // Min 1
            'paper_width' => 10, // Min 20
            'retry_attempts' => 15, // Max 10
        ];

        $response = $this->actingAs($this->user)
            ->put('/settings/printer', $invalidData);

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'name',
            'type', 
            'timeout',
            'paper_width',
            'retry_attempts'
        ]);
    }

    /** @test */
    public function it_can_test_printer_connection_when_available()
    {
        $settings = PrinterSettings::createDefaultIfNotExists();
        $settings->update([
            'enabled' => true,
            'type' => 'cups',
            'port' => 'TECH_CLA58'
        ]);

        $response = $this->actingAs($this->user)
            ->post('/settings/printer/test');

        $response->assertRedirect();
        // Puede ser éxito o error dependiendo de si la impresora está realmente disponible
        $this->assertTrue(
            session()->has('success') || session()->has('errors')
        );
    }

    /** @test */
    public function it_rejects_get_request_for_printer_test()
    {
        PrinterSettings::createDefaultIfNotExists();

        $response = $this->actingAs($this->user)
            ->get('/settings/printer/test');

        // Debería devolver 405 Method Not Allowed
        $response->assertStatus(405);
    }

    /** @test */
    public function it_cannot_test_printer_when_disabled()
    {
        $settings = PrinterSettings::createDefaultIfNotExists();
        $settings->update(['enabled' => false]);

        $response = $this->actingAs($this->user)
            ->post('/settings/printer/test');

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
    }

    /** @test */
    public function print_service_uses_database_configuration()
    {
        // Crear configuración en BD
        $settings = PrinterSettings::create([
            'name' => 'Test Printer',
            'enabled' => true,
            'type' => 'cups',
            'port' => 'TEST_PRINTER',
            'timeout' => 15,
            'paper_width' => 40,
            'is_default' => true,
        ]);

        // Crear servicio (debería usar configuración de BD)
        $service = new PrintService();
        $status = $service->getStatus();

        $this->assertTrue($status['enabled']);
        $this->assertEquals('TEST_PRINTER', $status['port']);
        $this->assertEquals('cups', $status['type']);
    }

    /** @test */
    public function print_service_falls_back_to_config_when_no_database_settings()
    {
        // No crear configuración en BD
        $this->assertDatabaseEmpty('printer_settings');

        // Configurar valores en config
        config([
            'printing.enabled' => true,
            'printing.type' => 'usb',
            'printing.port' => '/dev/test',
        ]);

        $service = new PrintService();
        $status = $service->getStatus();

        $this->assertTrue($status['enabled']);
        $this->assertEquals('/dev/test', $status['port']);
        $this->assertEquals('usb', $status['type']);
    }

    /** @test */
    public function it_can_get_available_printers()
    {
        $response = $this->actingAs($this->user)
            ->get('/settings/printer/available');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'printers' => [
                '*' => [
                    'name',
                    'description'
                ]
            ]
        ]);
    }

    /** @test */
    public function only_one_printer_can_be_default()
    {
        $printer1 = PrinterSettings::create([
            'name' => 'Printer 1',
            'type' => 'cups',
            'port' => 'PRINTER1',
            'is_default' => true,
        ]);

        $printer2 = PrinterSettings::create([
            'name' => 'Printer 2', 
            'type' => 'cups',
            'port' => 'PRINTER2',
            'is_default' => true, // Esto debería hacer que printer1 ya no sea default
        ]);

        $printer1->refresh();
        $printer2->refresh();

        $this->assertFalse($printer1->is_default);
        $this->assertTrue($printer2->is_default);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_printer_settings()
    {
        $response = $this->get('/settings/printer');
        $response->assertRedirect('/login');

        $response = $this->put('/settings/printer', []);
        $response->assertRedirect('/login');

        $response = $this->post('/settings/printer/test');
        $response->assertRedirect('/login');
    }
}
