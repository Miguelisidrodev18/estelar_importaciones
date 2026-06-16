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
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->foreignId('detalle_venta_id')
                  ->nullable()
                  ->after('variante_id')
                  ->constrained('detalle_ventas')
                  ->onDelete('set null');

            $table->index('detalle_venta_id');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropForeign(['detalle_venta_id']);
            $table->dropIndex(['detalle_venta_id']);
            $table->dropColumn('detalle_venta_id');
        });
    }
};
