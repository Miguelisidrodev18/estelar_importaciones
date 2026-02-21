<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: producto_unidades
 *
 * Almacena las presentaciones/unidades alternativas de un producto.
 * Ejemplo: 1 Caja = 12 Unidades, 1 Docena = 12 Unidades.
 *
 * La unidad base del producto ya está en productos.unidad_medida_id.
 * Esta tabla solo almacena las presentaciones ADICIONALES con su factor
 * de conversión respecto a la unidad base.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_unidades', function (Blueprint $table) {
            $table->id();

            // Producto al que pertenece esta presentación
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->cascadeOnDelete();

            // Unidad de medida de esta presentación
            $table->foreignId('unidad_id')
                  ->constrained('unidades_medida')
                  ->restrictOnDelete();

            // Factor de conversión: cuántas unidades base equivale esta presentación.
            // Ej: si la unidad base es "Unidad" y esta presentación es "Caja de 12", factor = 12.
            $table->decimal('factor', 12, 4)->default(1);

            // Nombre personalizado de la presentación (opcional).
            // Ej: "Caja x 12", "Docena", "Pack Promo"
            $table->string('nombre_presentacion', 100)->nullable();

            // Estado
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');

            $table->timestamps();

            // Un producto no puede tener la misma unidad dos veces
            $table->unique(['producto_id', 'unidad_id'], 'uq_producto_unidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_unidades');
    }
};
