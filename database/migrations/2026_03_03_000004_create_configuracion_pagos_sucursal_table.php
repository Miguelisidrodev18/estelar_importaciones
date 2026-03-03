<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('configuracion_pagos_sucursal')) {
            Schema::create('configuracion_pagos_sucursal', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
                $table->enum('tipo_pago', ['yape', 'plin', 'transferencia', 'pos']);
                $table->string('titular', 150)->nullable();
                $table->string('numero', 20)->nullable();       // Número de celular / cuenta
                $table->string('banco', 100)->nullable();       // Para transferencias
                $table->string('numero_cuenta', 50)->nullable();
                $table->string('cci', 30)->nullable();
                $table->string('qr_imagen_path', 300)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->unique(['sucursal_id', 'tipo_pago'], 'uq_sucursal_tipo_pago');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_pagos_sucursal');
    }
};
