<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comision_detalle_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detalle_venta_id')->constrained('detalle_ventas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('regla_id')->nullable()->constrained('comision_reglas')->nullOnDelete();
            $table->enum('tipo_calculo', ['porcentaje', 'monto_fijo']);
            $table->decimal('valor_configurado', 10, 4);
            $table->decimal('monto_comision', 10, 2);
            $table->enum('estado', ['pendiente', 'pagado'])->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->foreignId('pagado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('user_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comision_detalle_venta');
    }
};
