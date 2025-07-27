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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id()->comment('ID único de la factura');
            $table->string('code', 20)->unique()->comment('Código único de la factura (FV-00000001)');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict')->comment('ID del almacén asociado');
            $table->timestamps();

            // Índices para rendimiento
            $table->index('code', 'idx_invoices_code');
            $table->index('warehouse_id', 'idx_invoices_warehouse_id');
            $table->index(['created_at', 'updated_at'], 'idx_invoices_timestamps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
