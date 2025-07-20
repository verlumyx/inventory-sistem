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
        Schema::create('entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->constrained('entries')->onDelete('cascade')->comment('ID de la entrada');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade')->comment('ID del item');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade')->comment('ID del almacén');
            $table->decimal('amount', 10, 2)->comment('Cantidad del item');
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index(['entry_id']);
            $table->index(['item_id']);
            $table->index(['warehouse_id']);
            $table->index(['amount']);
            $table->index(['entry_id', 'item_id']);
            $table->index(['entry_id', 'warehouse_id']);

            // Índice único para evitar duplicados de item en la misma entrada
            $table->unique(['entry_id', 'item_id'], 'unique_entry_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_items');
    }
};
