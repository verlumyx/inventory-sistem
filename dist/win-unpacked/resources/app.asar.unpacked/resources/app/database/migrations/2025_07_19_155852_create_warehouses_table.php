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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Código único del almacén (WH-00000001)');
            $table->string('name', 255)->comment('Nombre del almacén');
            $table->text('description')->nullable()->comment('Descripción del almacén');
            $table->boolean('status')->default(true)->comment('Estado del almacén (activo/inactivo)');
            $table->timestamps();

            // Índices
            $table->index('code');
            $table->index('status');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
