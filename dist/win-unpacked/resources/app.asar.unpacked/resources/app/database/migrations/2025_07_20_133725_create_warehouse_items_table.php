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
        Schema::create('warehouse_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade')->comment('ID del almacén');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade')->comment('ID del item');
            $table->decimal('quantity_available', 10, 2)->default(0)->comment('Cantidad disponible del item en el almacén');
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index(['warehouse_id', 'item_id']);
            $table->index(['item_id', 'warehouse_id']);
            $table->index(['warehouse_id', 'quantity_available']);
            $table->index(['item_id', 'quantity_available']);

            // Constraint único para evitar duplicados
            $table->unique(['warehouse_id', 'item_id'], 'warehouse_item_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_items');
    }
};
