<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id()->comment('ID único del item de factura');
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade')->comment('ID de la factura');
            $table->foreignId('item_id')->constrained('items')->onDelete('restrict')->comment('ID del item');
            $table->decimal('amount', 10, 2)->comment('Cantidad del item');
            $table->decimal('price', 10, 2)->comment('Precio unitario del item');
            $table->timestamps();

            // Índices para rendimiento
            $table->index('invoice_id', 'idx_invoice_items_invoice_id');
            $table->index('item_id', 'idx_invoice_items_item_id');
            $table->index(['created_at', 'updated_at'], 'idx_invoice_items_timestamps');

            // Índice compuesto para evitar duplicados
            $table->unique(['invoice_id', 'item_id'], 'idx_invoice_items_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
