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
        Schema::create('printer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Impresora Principal');
            $table->boolean('enabled')->default(false);
            $table->enum('type', ['usb', 'serial', 'network', 'cups', 'macos'])->default('cups');
            $table->string('port')->nullable(); // Puerto, IP o nombre de impresora
            $table->string('printer_name')->nullable(); // Nombre específico para CUPS
            $table->integer('timeout')->default(5);

            // Configuración de red
            $table->string('network_host')->nullable();
            $table->integer('network_port')->default(9100);
            $table->integer('network_timeout')->default(10);

            // Configuración serial
            $table->integer('baud_rate')->default(9600);
            $table->integer('data_bits')->default(8);
            $table->integer('stop_bits')->default(1);
            $table->enum('parity', ['none', 'odd', 'even'])->default('none');
            $table->enum('flow_control', ['none', 'rts/cts', 'xon/xoff'])->default('none');

            // Configuración de papel
            $table->integer('paper_width')->default(32); // Caracteres por línea
            $table->integer('paper_margin')->default(0);
            $table->integer('line_spacing')->default(1);

            // Configuración de reintentos
            $table->boolean('retry_enabled')->default(true);
            $table->integer('retry_attempts')->default(3);
            $table->integer('retry_delay')->default(1);

            // Configuración de logging
            $table->boolean('log_enabled')->default(true);
            $table->enum('log_level', ['debug', 'info', 'warning', 'error'])->default('info');

            // Solo una configuración activa por vez
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            // Índices
            $table->index(['enabled', 'is_default']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_settings');
    }
};
