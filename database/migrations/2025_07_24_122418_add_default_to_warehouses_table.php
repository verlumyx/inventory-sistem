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
        Schema::table('warehouses', function (Blueprint $table) {
            $table->tinyInteger('default')->default(0)->after('status')->comment('Almacén por defecto (0 = No, 1 = Sí)');

            // Índice para mejorar el rendimiento en consultas por default
            $table->index('default', 'idx_warehouses_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropIndex('idx_warehouses_default');
            $table->dropColumn('default');
        });
    }
};
