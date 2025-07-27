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
        Schema::table('invoices', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->after('warehouse_id')->comment('Estado de la factura (0 = Por pagar, 1 = Pagada)');

            // Índice para mejorar el rendimiento en consultas por status
            $table->index('status', 'idx_invoices_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('idx_invoices_status');
            $table->dropColumn('status');
        });
    }
};
