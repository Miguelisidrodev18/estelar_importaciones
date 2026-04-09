<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Estado del documento frente a SUNAT
            $table->enum('estado_sunat', [
                'no_aplica',        // cotizaciones y NCs (nunca van a SUNAT directamente)
                'pendiente_envio',  // boleta/factura creada, aún no enviada a SUNAT
                'enviado',          // enviada a SUNAT, esperando CDR
                'aceptado',         // CDR aceptado por SUNAT → solo se puede cancelar con NC
                'rechazado',        // CDR rechazado por SUNAT → se puede corregir y reenviar
                'anulado_baja',     // Comunicación de Baja aceptada por SUNAT (boletas)
            ])->default('pendiente_envio')->after('estado_pago');

            // Para notas de crédito: referencia al comprobante de origen
            $table->foreignId('venta_origen_id')
                  ->nullable()
                  ->constrained('ventas')
                  ->nullOnDelete()
                  ->after('estado_sunat');

            // Motivo SUNAT de la nota de crédito (tabla 10 del estándar UBL)
            $table->string('motivo_nc_codigo', 2)->nullable()->after('venta_origen_id');
            $table->string('motivo_nc_descripcion', 200)->nullable()->after('motivo_nc_codigo');
        });

        // Cotizaciones existentes → no_aplica
        \DB::table('ventas')
            ->where('tipo_comprobante', 'cotizacion')
            ->update(['estado_sunat' => 'no_aplica']);
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['venta_origen_id']);
            $table->dropColumn(['estado_sunat', 'venta_origen_id', 'motivo_nc_codigo', 'motivo_nc_descripcion']);
        });
    }
};
