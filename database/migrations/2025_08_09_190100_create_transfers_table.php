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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Código único del traslado (TR-00000001)');
            $table->text('description')->nullable()->comment('Descripción del traslado');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete()->comment('Almacén asociado (opcional)');
            $table->foreignId('warehouse_source_id')->constrained('warehouses')->restrictOnDelete()->comment('ID del almacén origen');
            $table->foreignId('warehouse_destination_id')->constrained('warehouses')->restrictOnDelete()->comment('ID del almacén destino');
            $table->unsignedTinyInteger('status')->default(0)->comment('Estado del traslado: 0=Pendiente, 1=Completado');
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['code']);
            $table->index(['warehouse_id']);
            $table->index(['warehouse_source_id']);
            $table->index(['warehouse_destination_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
