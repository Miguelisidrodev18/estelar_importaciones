<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar si la tabla existe antes de modificarla
        if (Schema::hasTable('producto_precios')) {
            Schema::table('producto_precios', function (Blueprint $table) {
                // Precio de compra (costo)
                if (!Schema::hasColumn('producto_precios', 'precio_compra')) {
                    $table->decimal('precio_compra', 12, 2)->nullable()->after('precio');
                }

                // Precio mayorista
                if (!Schema::hasColumn('producto_precios', 'precio_mayorista')) {
                    $table->decimal('precio_mayorista', 12, 2)->nullable()->after('precio_compra');
                }

                // Margen
                if (!Schema::hasColumn('producto_precios', 'margen')) {
                    $table->decimal('margen', 5, 2)->nullable()->after('precio_mayorista');
                }

                // Observaciones
                if (!Schema::hasColumn('producto_precios', 'observaciones')) {
                    $table->text('observaciones')->nullable()->after('margen');
                }

                // Variante ID
                if (!Schema::hasColumn('producto_precios', 'variante_id')) {
                    $table->foreignId('variante_id')
                          ->nullable()
                          ->after('proveedor_id')
                          ->constrained('producto_variantes')
                          ->onDelete('cascade');
                }

                // Almacen ID
                if (!Schema::hasColumn('producto_precios', 'almacen_id')) {
                    $table->foreignId('almacen_id')
                          ->nullable()
                          ->after('variante_id')
                          ->constrained('almacenes')
                          ->onDelete('cascade');
                }

                // Índices
                try {
                    $table->index(['producto_id', 'almacen_id', 'tipo_precio', 'activo'], 'idx_pp_almacen_tipo');
                } catch (\Exception $e) {}

                try {
                    $table->index(['variante_id', 'almacen_id', 'activo'], 'idx_pp_variante_almacen');
                } catch (\Exception $e) {}
            });
        } else {
            // Si la tabla NO existe, la creamos completa
            Schema::create('producto_precios', function (Blueprint $table) {
                $table->id();
                $table->foreignId('producto_id')->constrained()->onDelete('cascade');
                $table->foreignId('proveedor_id')->nullable()->constrained('proveedores'); // CORREGIDO
                $table->foreignId('variante_id')->nullable()->constrained('producto_variantes')->onDelete('cascade');
                $table->foreignId('almacen_id')->nullable()->constrained('almacenes')->onDelete('cascade');
                
                $table->decimal('precio', 12, 2);
                $table->decimal('precio_compra', 12, 2)->nullable();
                $table->decimal('precio_mayorista', 12, 2)->nullable();
                $table->decimal('margen', 5, 2)->nullable();
                $table->text('observaciones')->nullable();
                
                $table->string('tipo_precio')->default('regular');
                $table->date('fecha_inicio')->nullable();
                $table->date('fecha_fin')->nullable();
                $table->integer('prioridad')->default(0);
                $table->boolean('activo')->default(true);
                
                $table->timestamps();
                
                $table->index(['producto_id', 'almacen_id', 'tipo_precio', 'activo'], 'idx_pp_almacen_tipo');
                $table->index(['variante_id', 'almacen_id', 'activo'], 'idx_pp_variante_almacen');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('producto_precios')) {
            Schema::table('producto_precios', function (Blueprint $table) {
                // Eliminar índices
                try {
                    $table->dropIndex('idx_pp_almacen_tipo');
                } catch (\Exception $e) {}
                
                try {
                    $table->dropIndex('idx_pp_variante_almacen');
                } catch (\Exception $e) {}
                
                // Eliminar foreign keys
                try {
                    $table->dropForeign(['variante_id']);
                } catch (\Exception $e) {}
                
                try {
                    $table->dropForeign(['almacen_id']);
                } catch (\Exception $e) {}
                
                // Eliminar columnas
                $columns = ['precio_compra', 'precio_mayorista', 'margen', 'observaciones', 'variante_id', 'almacen_id'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('producto_precios', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};