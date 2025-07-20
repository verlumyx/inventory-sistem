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
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Código único de la entrada (ET-00000001)');
            $table->string('name', 255)->comment('Nombre de la entrada');
            $table->text('description')->nullable()->comment('Descripción de la entrada');
            $table->boolean('status')->default(true)->comment('Estado de la entrada (activo/inactivo)');
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index(['status', 'created_at']);
            $table->index(['name']);
            $table->index(['code']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
