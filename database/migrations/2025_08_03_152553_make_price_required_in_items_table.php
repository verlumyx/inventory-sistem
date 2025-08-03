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
            // Primero actualizar los registros existentes con precio null a 0.01
            \DB::table('items')->whereNull('price')->update(['price' => 0.01]);

            // Luego hacer el campo no nullable
            $table->decimal('price', 10, 2)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Revertir el campo a nullable
            $table->decimal('price', 10, 2)->nullable()->change();
        });
    }
};
