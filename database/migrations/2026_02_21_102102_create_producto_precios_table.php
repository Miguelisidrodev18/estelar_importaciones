<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si la tabla ya existe (creada manualmente o en sesión anterior), la eliminamos
        // para recrearla con el esquema correcto.
        Schema::dropIfExists('precios_producto');

        Schema::create('precios_producto', function (Blueprint $table) {
            $table->id();
            
            // Relación con producto
            $table->foreignId('producto_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // 🔴 IMPORTANTE: Relación con la unidad de medida específica
            $table->foreignId('producto_unidad_id')
                  ->nullable()
                  ->constrained('producto_unidades')
                  ->onDelete('cascade');
            
            // Tipo de precio
            $table->enum('tipo_precio', [
                'venta_regular',
                'venta_mayorista',
                'venta_oferta',
                'venta_especial',
                'compra_ultimo',
                'compra_promedio'
            ])->default('venta_regular');
            
            // Valor del precio
            $table->decimal('precio', 10, 2);
            
            // Moneda
            $table->string('moneda', 3)->default('PEN');
            
            // Fechas de vigencia
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            
            // Condiciones de cantidad
            $table->integer('cantidad_minima')->nullable();
            $table->integer('cantidad_maxima')->nullable();
            
            // Cliente específico
            $table->foreignId('cliente_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('set null');
            
            // Proveedor
            $table->foreignId('proveedor_id')
                  ->nullable()
                  ->constrained('proveedores')
                  ->onDelete('set null');
            
            // Prioridad
            $table->integer('prioridad')->default(0);
            
            // Estado
            $table->boolean('activo')->default(true);
            
            // Auditoría
            $table->foreignId('creado_por')
                  ->nullable()
                  ->constrained('users');
            
            $table->timestamps();
            
            // 🔴 ÍNDICES CON NOMBRES MÁS CORTOS
            $table->index(['producto_id', 'tipo_precio', 'activo'], 'idx_precios_producto_tipo');
            $table->index(['producto_id', 'fecha_inicio', 'fecha_fin'], 'idx_precios_vigencia');
            $table->index('cliente_id', 'idx_precios_cliente');
            $table->index('proveedor_id', 'idx_precios_proveedor');
            $table->index('producto_unidad_id', 'idx_precios_unidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precios_producto');
    }
};