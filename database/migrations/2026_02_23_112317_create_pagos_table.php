<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_por_pagar_id')->constrained('cuentas_por_pagar')->onDelete('cascade');
            $table->decimal('monto', 12, 2);
            $table->date('fecha_pago');
            $table->enum('metodo_pago', ['transferencia', 'cheque', 'efectivo', 'tarjeta'])->default('transferencia');
            $table->string('referencia', 100)->nullable();
            $table->string('banco_origen', 100)->nullable();
            $table->string('cuenta_origen', 50)->nullable();
            $table->enum('estado', ['programado', 'procesado', 'fallido'])->default('procesado');
            $table->date('fecha_programacion')->nullable();
            $table->foreignId('usuario_id')->constrained('users');
            $table->text('observaciones')->nullable();
            $table->string('comprobante_path', 255)->nullable();
            $table->timestamps();

            $table->index('fecha_pago');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};