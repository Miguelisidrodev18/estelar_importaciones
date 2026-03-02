<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Tipo de comprobante: boleta, factura, cotizacion
            $table->string('tipo_comprobante', 20)->default('boleta')->after('metodo_pago');

            // Guía de remisión (envíos a provincia, facturas)
            $table->string('guia_remision', 100)->nullable()->after('tipo_comprobante');
            $table->string('transportista', 150)->nullable()->after('guia_remision');
            $table->string('placa_vehiculo', 20)->nullable()->after('transportista');

            // Pagos mixtos (JSON con detalle de cada método + monto)
            $table->json('pagos_detalle')->nullable()->after('placa_vehiculo');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_comprobante',
                'guia_remision',
                'transportista',
                'placa_vehiculo',
                'pagos_detalle',
            ]);
        });
    }
};
