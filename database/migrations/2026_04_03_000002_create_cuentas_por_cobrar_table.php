<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_por_cobrar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->unique()->constrained('ventas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->decimal('monto_total', 12, 2);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->unsignedSmallInteger('numero_cuotas');
            $table->unsignedSmallInteger('dias_entre_cuotas')->default(30);
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento_final');
            $table->enum('estado', ['vigente', 'vencido', 'pagado', 'anulado'])->default('vigente');
            $table->date('fecha_ultimo_pago')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['estado', 'fecha_vencimiento_final']);
            $table->index('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_por_cobrar');
    }
};
