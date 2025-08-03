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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('rate', 10, 4)->default(1.0000)->comment('Tasa de cambio oficial');
            $table->timestamps();
        });

        // Insertar registro inicial con tasa de cambio 1
        \App\Inventory\ExchangeRate\Models\ExchangeRate::create([
            'rate' => 1.0000,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
