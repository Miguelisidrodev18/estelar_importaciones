<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: precios_producto
 *
 * Módulo de Gestión de Precios (futuro).
 * Gestiona los precios de venta de cada producto por presentación y tipo de precio.
 *
 * - Un producto puede tener precios en su unidad base y en cada presentación alternativa.
 * - Se soportan múltiples tipos de precio: venta al público, mayoreo, especial, etc.
 * - Los precios pueden tener vigencia definida (vigente_desde / vigente_hasta).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precios_producto', function (Blueprint $table) {
            $table->id();

            // Producto al que pertenece el precio
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->cascadeOnDelete();

            // Presentación a la que aplica el precio.
            // NULL = precio en la unidad base del producto.
            $table->foreignId('producto_unidad_id')
                  ->nullable()
                  ->constrained('producto_unidades')
                  ->nullOnDelete();

            // Tipo de precio
            $table->enum('tipo_precio', [
                'venta',        // Precio normal al público
                'mayoreo',      // Precio por volumen
                'especial',     // Precio negociado para cliente específico
                'promocion',    // Precio temporal de promoción
            ])->default('venta');

            // Precio de venta
            $table->decimal('precio', 12, 2);

            // Período de vigencia (ambos opcionales; NULL = sin límite)
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable();

            // Estado del precio
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');

            // Auditoría
            $table->foreignId('creado_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            // Índices para consultas frecuentes
            $table->index(['producto_id', 'tipo_precio', 'estado']);
            $table->index(['producto_id', 'producto_unidad_id', 'tipo_precio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precios_producto');
    }
};
