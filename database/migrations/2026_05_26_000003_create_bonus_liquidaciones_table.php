<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonus_liquidaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bonus_regla_id')->nullable()->constrained('bonus_reglas')->nullOnDelete();

            // 'fijo' o 'meta'
            $table->enum('tipo_origen', ['fijo', 'meta']);

            // Solo para bonos fijos — uno por detalle_venta
            $table->foreignId('detalle_venta_id')->nullable()->constrained('detalle_ventas')->cascadeOnDelete();

            // Solo para bonos de meta — período evaluado
            $table->date('periodo_inicio')->nullable();
            $table->date('periodo_fin')->nullable();
            $table->unsignedInteger('unidades_periodo')->nullable();

            // Snapshots
            $table->enum('tipo_calculo', ['monto_fijo', 'porcentaje_venta'])->nullable();
            $table->decimal('valor_configurado', 10, 4)->nullable();
            $table->decimal('monto_bonus', 10, 2);

            // Pago
            $table->enum('estado', ['pendiente', 'pagado'])->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->foreignId('pagado_por_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Índices
            $table->index(['user_id', 'estado']);
            $table->index('tipo_origen');

            // Evitar duplicar bonos de meta para el mismo período
            $table->unique(['user_id', 'bonus_regla_id', 'periodo_inicio'], 'uq_bonus_meta_periodo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_liquidaciones');
    }
};
