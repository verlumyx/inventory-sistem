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
        Schema::create('license_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('action'); // 'generated', 'activated', 'expired', 'access_attempt', 'validation_failed'
            $table->string('license_code')->nullable();
            $table->string('machine_id')->nullable();
            $table->string('user_email')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable(); // Información adicional
            $table->enum('level', ['info', 'warning', 'error'])->default('info');
            $table->text('message')->nullable();
            $table->timestamps();

            // Índices para consultas eficientes
            $table->index(['action', 'created_at']);
            $table->index(['license_code', 'created_at']);
            $table->index(['machine_id', 'created_at']);
            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_logs');
    }
};
