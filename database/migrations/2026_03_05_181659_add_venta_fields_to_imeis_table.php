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
        Schema::table('imeis', function (Blueprint $table) {
            $table->unsignedBigInteger('venta_id')->nullable()->after('detalle_compra_id');
            $table->date('fecha_venta')->nullable()->after('fecha_ingreso');

            $table->foreign('venta_id')->references('id')->on('ventas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('imeis', function (Blueprint $table) {
            $table->dropForeign(['venta_id']);
            $table->dropColumn(['venta_id', 'fecha_venta']);
        });
    }
};
