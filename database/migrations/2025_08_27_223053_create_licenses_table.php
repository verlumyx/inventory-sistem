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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('license_code', 10)->unique();
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->enum('status', ['pending', 'active', 'expired', 'revoked'])->default('pending');
            $table->string('machine_id')->nullable();
            $table->string('user_email')->nullable();
            $table->datetime('activated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['status', 'end_date']);
            $table->index('machine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
