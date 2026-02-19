<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración consolidada de productos.
 *
 * Reemplaza las 3 migraciones conflictivas:
 *  - fix_productos_add_marca_modelo_fks
 *  - modify_productos_table
 *  - remove_obsolete_fields_from_productos
 *
 * Usa hasColumn() en cada paso → idempotente sin importar el estado previo de la BD.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── PASO 1: Eliminar campos string/precio obsoletos ─────────────────
        // Se eliminan en llamadas separadas para evitar conflictos con las FKs
        // que se agregarán en pasos siguientes.
        Schema::table('productos', function (Blueprint $table) {
            // marca/modelo string → reemplazados por FKs a catálogos
            if (Schema::hasColumn('productos', 'marca')) {
                $table->dropColumn('marca');
            }
            if (Schema::hasColumn('productos', 'modelo')) {
                $table->dropColumn('modelo');
            }
            // unidad_medida string → reemplazado por unidad_medida_id FK
            if (Schema::hasColumn('productos', 'unidad_medida')) {
                $table->dropColumn('unidad_medida');
            }
        });

        Schema::table('productos', function (Blueprint $table) {
            // Precios → se gestionan en el módulo de ventas/compras
            if (Schema::hasColumn('productos', 'precio_compra_actual')) {
                $table->dropColumn('precio_compra_actual');
            }
            if (Schema::hasColumn('productos', 'precio_venta')) {
                $table->dropColumn('precio_venta');
            }
            if (Schema::hasColumn('productos', 'precio_mayorista')) {
                $table->dropColumn('precio_mayorista');
            }
        });

        // ─── PASO 2: Agregar FKs a catálogos ────────────────────────────────
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'marca_id')) {
                $table->foreignId('marca_id')
                      ->nullable()
                      ->after('categoria_id')
                      ->constrained('marcas')
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('productos', 'modelo_id')) {
                $table->foreignId('modelo_id')
                      ->nullable()
                      ->after('marca_id')
                      ->constrained('modelos')
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('productos', 'color_id')) {
                $table->foreignId('color_id')
                      ->nullable()
                      ->after('modelo_id')
                      ->constrained('colores')
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('productos', 'unidad_medida_id')) {
                $table->foreignId('unidad_medida_id')
                      ->nullable()
                      ->after('color_id')
                      ->constrained('unidades_medida')
                      ->nullOnDelete();
            }
        });

        // ─── PASO 3: Migrar tipo_producto → tipo_inventario ──────────────────
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'tipo_inventario')) {
                $table->enum('tipo_inventario', ['cantidad', 'serie'])
                      ->default('cantidad')
                      ->after('unidad_medida_id');
            }
        });

        // Copiar datos de tipo_producto al nuevo campo
        if (Schema::hasColumn('productos', 'tipo_producto')) {
            \DB::table('productos')->where('tipo_producto', 'celular')
               ->update(['tipo_inventario' => 'serie']);
            \DB::table('productos')->where('tipo_producto', 'accesorio')
               ->update(['tipo_inventario' => 'cantidad']);

            Schema::table('productos', function (Blueprint $table) {
                $table->dropIndex(['tipo_producto']);
                $table->dropColumn('tipo_producto');
            });
        }

        // ─── PASO 4: Agregar campos de garantía ──────────────────────────────
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'dias_garantia')) {
                $table->integer('dias_garantia')
                      ->default(0)
                      ->after('tipo_inventario');
            }

            if (!Schema::hasColumn('productos', 'tipo_garantia')) {
                $table->enum('tipo_garantia', ['proveedor', 'tienda', 'fabricante'])
                      ->nullable()
                      ->after('dias_garantia');
            }
        });

        // ─── PASO 5: Agregar campos de costo (solo lectura, calculados automáticamente) ──
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'costo_promedio')) {
                $table->decimal('costo_promedio', 10, 2)
                      ->default(0)
                      ->after('tipo_garantia');
            }

            if (!Schema::hasColumn('productos', 'ultimo_costo_compra')) {
                $table->decimal('ultimo_costo_compra', 10, 2)
                      ->default(0)
                      ->after('costo_promedio');
            }

            if (!Schema::hasColumn('productos', 'fecha_ultima_compra')) {
                $table->date('fecha_ultima_compra')
                      ->nullable()
                      ->after('ultimo_costo_compra');
            }
        });

        // ─── PASO 6: Agregar campos de auditoría ─────────────────────────────
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'creado_por')) {
                $table->foreignId('creado_por')
                      ->nullable()
                      ->after('fecha_ultima_compra')
                      ->constrained('users')
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('productos', 'modificado_por')) {
                $table->foreignId('modificado_por')
                      ->nullable()
                      ->after('creado_por')
                      ->constrained('users')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // ─── Revertir auditoría ───────────────────────────────────────────────
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'modificado_por')) {
                $table->dropForeign(['modificado_por']);
                $table->dropColumn('modificado_por');
            }
            if (Schema::hasColumn('productos', 'creado_por')) {
                $table->dropForeign(['creado_por']);
                $table->dropColumn('creado_por');
            }
        });

        // ─── Revertir campos de costo ─────────────────────────────────────────
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'fecha_ultima_compra')) {
                $table->dropColumn('fecha_ultima_compra');
            }
            if (Schema::hasColumn('productos', 'ultimo_costo_compra')) {
                $table->dropColumn('ultimo_costo_compra');
            }
            if (Schema::hasColumn('productos', 'costo_promedio')) {
                $table->dropColumn('costo_promedio');
            }
        });

        // ─── Revertir campos de garantía ─────────────────────────────────────
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'tipo_garantia')) {
                $table->dropColumn('tipo_garantia');
            }
            if (Schema::hasColumn('productos', 'dias_garantia')) {
                $table->dropColumn('dias_garantia');
            }
        });

        // ─── Revertir tipo de inventario ─────────────────────────────────────
        if (!Schema::hasColumn('productos', 'tipo_producto')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->enum('tipo_producto', ['celular', 'accesorio'])
                      ->default('accesorio')
                      ->after('nombre');
                $table->index('tipo_producto');
            });
            \DB::table('productos')->where('tipo_inventario', 'serie')
               ->update(['tipo_producto' => 'celular']);
            \DB::table('productos')->where('tipo_inventario', 'cantidad')
               ->update(['tipo_producto' => 'accesorio']);
        }
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'tipo_inventario')) {
                $table->dropColumn('tipo_inventario');
            }
        });

        // ─── Revertir FKs del catálogo ───────────────────────────────────────
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'unidad_medida_id')) {
                $table->dropForeign(['unidad_medida_id']);
                $table->dropColumn('unidad_medida_id');
            }
            if (Schema::hasColumn('productos', 'color_id')) {
                $table->dropForeign(['color_id']);
                $table->dropColumn('color_id');
            }
            if (Schema::hasColumn('productos', 'modelo_id')) {
                $table->dropForeign(['modelo_id']);
                $table->dropColumn('modelo_id');
            }
            if (Schema::hasColumn('productos', 'marca_id')) {
                $table->dropForeign(['marca_id']);
                $table->dropColumn('marca_id');
            }
        });

        // ─── Restaurar campos obsoletos (estado original) ────────────────────
        Schema::table('productos', function (Blueprint $table) {
            $table->string('marca', 100)->nullable()->after('categoria_id');
            $table->string('modelo', 100)->nullable()->after('marca');
            $table->string('unidad_medida', 20)->default('unidad')->after('modelo');
            $table->decimal('precio_compra_actual', 10, 2)->default(0);
            $table->decimal('precio_venta', 10, 2)->default(0);
            $table->decimal('precio_mayorista', 10, 2)->nullable();
        });
    }
};
