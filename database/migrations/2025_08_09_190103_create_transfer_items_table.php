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
        Schema::create('transfer_items', function (Blueprint $table) {
            $table->id()->comment('ID único del item de traslado');
            $table->foreignId('transfer_id')->constrained('transfers')->onDelete('cascade')->comment('ID del traslado');
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete()->comment('ID del item');
            $table->decimal('amount', 10, 2)->comment('Cantidad a trasladar');
            $table->timestamps();

            $table->index(['transfer_id', 'item_id']);
            $table->index(['item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_items');
    }
};
