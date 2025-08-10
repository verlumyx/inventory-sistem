<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adjustment_items', function (Blueprint $table) {
            $table->id()->comment('ID único del detalle de ajuste');
            $table->foreignId('adjustment_id')->constrained('adjustments')->onDelete('cascade')->comment('ID del ajuste');
            $table->foreignId('item_id')->constrained('items')->onDelete('restrict')->comment('ID del item');
            $table->decimal('amount', 10, 2)->comment('Cantidad ajustada (positiva o negativa)');
            $table->timestamps();

            $table->index('adjustment_id', 'idx_adjustment_items_adjustment_id');
            $table->index('item_id', 'idx_adjustment_items_item_id');
            $table->index(['created_at', 'updated_at'], 'idx_adjustment_items_timestamps');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjustment_items');
    }
};

