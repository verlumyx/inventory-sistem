<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            // Cambiar status de boolean a tinyInteger
            // 0 = Por recibir, 1 = Recibido
            $table->tinyInteger('status')->default(0)->change()->comment('Estado de la entrada (0=Por recibir, 1=Recibido)');
        });

        // Actualizar registros existentes: true -> 1, false -> 0
        DB::statement('UPDATE entries SET status = CASE WHEN status = 1 THEN 1 ELSE 0 END');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            // Revertir a boolean
            $table->boolean('status')->default(true)->change()->comment('Estado de la entrada (activo/inactivo)');
        });
    }
};
