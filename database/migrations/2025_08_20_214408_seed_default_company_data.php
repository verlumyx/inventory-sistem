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
        // Insertar datos por defecto de la empresa
        DB::table('companies')->insert([
            'name_company' => 'Mi Empresa',
            'dni' => '12345678-9',
            'address' => 'Dirección de la empresa',
            'phone' => '+58 412-123-4567',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar el registro por defecto
        DB::table('companies')->where('name_company', 'Mi Empresa')->delete();
    }
};
