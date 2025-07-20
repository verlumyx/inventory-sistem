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
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('description')->comment('Precio del item');
            $table->string('unit', 50)->nullable()->after('price')->comment('Unidad de medida del item (ej: pcs, kg, m, etc.)');

            // Índices para mejorar el rendimiento
            $table->index(['price']);
            $table->index(['unit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['price']);
            $table->dropIndex(['unit']);
            $table->dropColumn(['price', 'unit']);
        });
    }
};
