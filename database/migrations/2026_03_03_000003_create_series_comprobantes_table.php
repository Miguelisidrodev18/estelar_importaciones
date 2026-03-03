<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('series_comprobantes')) {
            Schema::create('series_comprobantes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
                // Código SUNAT del tipo de comprobante
                $table->string('tipo_comprobante', 5);  // 01 Factura, 03 Boleta, 07 NC, 08 ND, 09 Guía Remisión
                $table->string('tipo_nombre', 80);       // "Factura Electrónica", etc.
                $table->string('serie', 5);              // FA01, BA01, FC01…
                $table->unsignedInteger('correlativo_actual')->default(1);
                $table->enum('formato_impresion', ['A4', 'ticket', 'A5'])->default('A4');
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->unique(['sucursal_id', 'serie'], 'uq_sucursal_serie');
                $table->index(['sucursal_id', 'tipo_comprobante']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('series_comprobantes');
    }
};
