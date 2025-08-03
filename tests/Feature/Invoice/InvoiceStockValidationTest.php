<?php

namespace Tests\Feature\Invoice;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Inventory\Invoice\Models\Invoice;
use App\Inventory\Invoice\Models\InvoiceItem;
use App\Inventory\Invoice\Services\InvoiceStockValidator;
use App\Inventory\Invoice\Exceptions\InsufficientStockException;
use App\Inventory\Warehouse\Models\Warehouse;
use App\Inventory\Item\Models\Item;
use App\Inventory\WarehouseItem\Models\WarehouseItem;

class InvoiceStockValidationTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceStockValidator $validator;
    private Warehouse $warehouse;
    private Item $item;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->validator = new InvoiceStockValidator();
        
        // Create test data
        $this->warehouse = Warehouse::factory()->create();
        $this->item = Item::factory()->create();
        $this->invoice = Invoice::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'status' => 0, // pending
        ]);
    }

    /** @test */
    public function it_passes_validation_when_stock_is_sufficient()
    {
        // Arrange: Create warehouse item with sufficient stock
        WarehouseItem::create([
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item->id,
            'quantity_available' => 100.00,
        ]);

        // Create invoice item requesting less than available
        InvoiceItem::create([
            'invoice_id' => $this->invoice->id,
            'item_id' => $this->item->id,
            'amount' => 50.00,
            'price' => 10.00,
        ]);

        // Act & Assert: Should not throw exception
        $this->validator->validateStockForPayment($this->invoice->fresh(['invoiceItems.item']));
        
        $this->assertTrue(true); // If we reach here, validation passed
    }

    /** @test */
    public function it_throws_exception_when_stock_is_insufficient()
    {
        // Arrange: Create warehouse item with insufficient stock
        WarehouseItem::create([
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item->id,
            'quantity_available' => 30.00,
        ]);

        // Create invoice item requesting more than available
        InvoiceItem::create([
            'invoice_id' => $this->invoice->id,
            'item_id' => $this->item->id,
            'amount' => 50.00,
            'price' => 10.00,
        ]);

        // Act & Assert: Should throw InsufficientStockException
        $this->expectException(InsufficientStockException::class);
        
        $this->validator->validateStockForPayment($this->invoice->fresh(['invoiceItems.item']));
    }

    /** @test */
    public function it_throws_exception_when_item_has_no_stock_record()
    {
        // Arrange: Don't create warehouse item (no stock record)
        
        // Create invoice item
        InvoiceItem::create([
            'invoice_id' => $this->invoice->id,
            'item_id' => $this->item->id,
            'amount' => 10.00,
            'price' => 10.00,
        ]);

        // Act & Assert: Should throw InsufficientStockException
        $this->expectException(InsufficientStockException::class);
        
        $this->validator->validateStockForPayment($this->invoice->fresh(['invoiceItems.item']));
    }

    /** @test */
    public function it_provides_detailed_stock_information()
    {
        // Arrange: Create warehouse item
        WarehouseItem::create([
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item->id,
            'quantity_available' => 30.00,
        ]);

        // Create invoice item requesting more than available
        InvoiceItem::create([
            'invoice_id' => $this->invoice->id,
            'item_id' => $this->item->id,
            'amount' => 50.00,
            'price' => 10.00,
        ]);

        // Act
        $stockInfo = $this->validator->getStockInformation($this->invoice->fresh(['invoiceItems.item']));

        // Assert
        $this->assertCount(1, $stockInfo);
        $this->assertEquals($this->item->id, $stockInfo[0]['item_id']);
        $this->assertEquals(50.00, $stockInfo[0]['requested']);
        $this->assertEquals(30.00, $stockInfo[0]['available']);
        $this->assertFalse($stockInfo[0]['sufficient']);
        $this->assertEquals(20.00, $stockInfo[0]['shortage']);
    }
}
