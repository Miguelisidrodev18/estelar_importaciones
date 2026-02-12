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
            // Agregar referencia a IMEI (solo para celulares)
            $table->foreignId('imei_id')->nullable()->after('producto_id')->constrained('imeis')->onDelete('set null');
            
            // Agregar estado para transferencias (requieren confirmación)
            $table->enum('estado', ['completado', 'pendiente', 'confirmado', 'cancelado'])->default('completado')->after('observaciones');
            
            // Índices
            $table->index('imei_id');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropForeign(['imei_id']);
            $table->dropColumn(['imei_id', 'estado']);
        });
    }
};