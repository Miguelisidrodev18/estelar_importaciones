<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonus_reglas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);

            // Aplica a producto o categoría
            $table->enum('tipo_aplicacion', ['producto', 'categoria']);
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();

            // Tipo de bono
            $table->enum('tipo_bonus', ['fijo', 'meta']);

            // Cálculo del monto del bono
            $table->enum('tipo_calculo', ['monto_fijo', 'porcentaje_venta']);
            $table->decimal('valor', 10, 4)->comment('Monto fijo S/ o porcentaje %');

            // Solo para tipo_bonus = meta
            $table->unsignedInteger('meta_unidades')->nullable()
                ->comment('Unidades mínimas en el período para ganar el bono');
            $table->enum('meta_periodo', ['mensual', 'quincenal', 'semanal'])->nullable()->default('mensual');

            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_reglas');
    }
};
