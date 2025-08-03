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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Código único del item (IT-00000001)');
            $table->string('name', 255)->comment('Nombre del item');
            $table->string('qr_code', 255)->nullable()->unique()->comment('Código de barra del item');
            $table->text('description')->nullable()->comment('Descripción del item');
            $table->boolean('status')->default(true)->comment('Estado del item (activo/inactivo)');
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index(['status', 'created_at']);
            $table->index(['name']);
            $table->index(['code']);
            $table->index(['qr_code']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
