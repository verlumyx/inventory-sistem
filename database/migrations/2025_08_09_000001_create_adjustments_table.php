<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adjustments', function (Blueprint $table) {
            $table->id()->comment('ID único del ajuste');
            $table->string('code', 20)->unique()->comment('Código único del ajuste (AJ-00000001)');
            $table->text('description')->nullable()->comment('Descripción del ajuste');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict')->comment('Almacén del ajuste');
            $table->string('type', 20)->default('positive')->after('warehouse_id');
            $table->tinyInteger('status')->default(0)->comment('Estado del ajuste (0=Pendiente, 1=Aplicado)');
            $table->timestamps();

            $table->index('code', 'idx_adjustments_code');
            $table->index('warehouse_id', 'idx_adjustments_warehouse_id');
            $table->index('status', 'idx_adjustments_status');
            $table->index(['created_at', 'updated_at'], 'idx_adjustments_timestamps');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjustments');
    }
};

