<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            // Datos financieros
            $table->enum('forma_pago', ['contado', 'credito', 'tarjeta', 'transferencia', 'cheque'])
                  ->default('contado')
                  ->after('total');
            
            $table->integer('condicion_pago')->nullable()->after('forma_pago');
            $table->date('fecha_vencimiento')->nullable()->after('condicion_pago');
            
            $table->enum('tipo_moneda', ['PEN', 'USD'])->default('PEN')->after('fecha_vencimiento');
            $table->decimal('tipo_cambio', 10, 4)->default(1)->after('tipo_moneda');
            $table->boolean('incluye_igv')->default(true)->after('tipo_cambio');
            
            $table->decimal('descuento_global', 10, 2)->default(0)->after('incluye_igv');
            $table->decimal('monto_adicional', 10, 2)->default(0)->after('descuento_global');
            $table->string('concepto_adicional', 255)->nullable()->after('monto_adicional');
            
            // Datos de envío
            $table->string('guia_remision', 50)->nullable()->after('concepto_adicional');
            $table->string('transportista', 255)->nullable()->after('guia_remision');
            $table->string('placa_vehiculo', 10)->nullable()->after('transportista');
            
            // Datos de anulación
            $table->datetime('fecha_anulacion')->nullable()->after('placa_vehiculo');
            $table->text('motivo_anulacion')->nullable()->after('fecha_anulacion');
            
            // Tipo de operación SUNAT (el que intentábamos agregar)
            $table->enum('tipo_operacion', ['01', '02', '03', '04'])
                  ->default('01')
                  ->after('tipo_cambio');
            $table->string('tipo_operacion_texto', 100)->nullable()->after('tipo_operacion');
        });
    }

    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $columns = [
                'forma_pago',
                'condicion_pago',
                'fecha_vencimiento',
                'tipo_moneda',
                'tipo_cambio',
                'incluye_igv',
                'descuento_global',
                'monto_adicional',
                'concepto_adicional',
                'guia_remision',
                'transportista',
                'placa_vehiculo',
                'fecha_anulacion',
                'motivo_anulacion',
                'tipo_operacion',
                'tipo_operacion_texto'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('compras', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};