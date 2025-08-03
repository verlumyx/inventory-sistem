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

        \App\Inventory\Warehouse\Models\Warehouse::create([
            'name' => 'Almacén Principal',
            'description' => 'Almacén principal de la empresa para productos generales',
            'status' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Inventory\Warehouse\Models\Warehouse::where('id', '>', 0)->delete();
    }
};
