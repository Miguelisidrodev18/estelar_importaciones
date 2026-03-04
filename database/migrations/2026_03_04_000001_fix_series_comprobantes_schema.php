<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Agregar nuevas columnas SOLO si NO existen ──────────────────
        Schema::table('series_comprobantes', function (Blueprint $table) {
            if (!Schema::hasColumn('series_comprobantes', 'tipo_comprobante')) {
                $table->string('tipo_comprobante', 5)->nullable()->after('sucursal_id');
            }
            if (!Schema::hasColumn('series_comprobantes', 'tipo_nombre')) {
                $table->string('tipo_nombre', 80)->nullable()->after('tipo_comprobante');
            }
            if (!Schema::hasColumn('series_comprobantes', 'formato_impresion')) {
                $table->string('formato_impresion', 10)->nullable()->after('correlativo_actual');
            }
            if (!Schema::hasColumn('series_comprobantes', 'activo')) {
                $table->boolean('activo')->default(true)->after('formato_impresion');
            }
        });

        // ── 2. Migrar datos de columnas viejas a nuevas ─────────────────────────
        // Solo ejecutar si las columnas nuevas existen y las viejas también
        if (Schema::hasColumn('series_comprobantes', 'tipo') && 
            Schema::hasColumn('series_comprobantes', 'tipo_comprobante')) {
            DB::statement("
                UPDATE series_comprobantes SET
                    tipo_comprobante = COALESCE(tipo, tipo_comprobante)
                WHERE tipo_comprobante IS NULL AND tipo IS NOT NULL
            ");
        }

        if (Schema::hasColumn('series_comprobantes', 'descripcion') && 
            Schema::hasColumn('series_comprobantes', 'tipo_nombre')) {
            DB::statement("
                UPDATE series_comprobantes SET
                    tipo_nombre = CASE 
                        WHEN tipo_comprobante = '01' THEN 'Factura Electrónica'
                        WHEN tipo_comprobante = '03' THEN 'Boleta de Venta Electrónica'
                        WHEN tipo_comprobante = '07' THEN 'Nota de Crédito'
                        WHEN tipo_comprobante = '08' THEN 'Nota de Débito'
                        WHEN tipo_comprobante = '09' THEN 'Guía de Remisión Remitente'
                        WHEN tipo_comprobante = 'NE' THEN 'Nota de Entrega/Cotización'
                        ELSE COALESCE(descripcion, tipo_nombre)
                    END
                WHERE tipo_nombre IS NULL
            ");
        }

        if (Schema::hasColumn('series_comprobantes', 'formato') && 
            Schema::hasColumn('series_comprobantes', 'formato_impresion')) {
            DB::statement("
                UPDATE series_comprobantes SET
                    formato_impresion = CASE
                        WHEN formato = 'a4' OR formato = 'A4' THEN 'A4'
                        ELSE 'ticket'
                    END
                WHERE formato_impresion IS NULL
            ");
        }

        if (Schema::hasColumn('series_comprobantes', 'activa') && 
            Schema::hasColumn('series_comprobantes', 'activo')) {
            DB::statement("
                UPDATE series_comprobantes SET
                    activo = activa
                WHERE activo IS NULL
            ");
        }

        // ── 3. Modificar columnas para NOT NULL (solo si existen) ─────────
        try {
            DB::statement("ALTER TABLE series_comprobantes MODIFY tipo_comprobante VARCHAR(5) NOT NULL");
        } catch (\Exception $e) {
            // La columna ya es NOT NULL o no existe
        }

        try {
            DB::statement("ALTER TABLE series_comprobantes MODIFY tipo_nombre VARCHAR(80) NOT NULL");
        } catch (\Exception $e) {}

        try {
            DB::statement("ALTER TABLE series_comprobantes MODIFY formato_impresion VARCHAR(10) NOT NULL DEFAULT 'A4'");
        } catch (\Exception $e) {}

        try {
            DB::statement("ALTER TABLE series_comprobantes MODIFY serie VARCHAR(5) NOT NULL");
        } catch (\Exception $e) {}

        // ── 4. Eliminar columnas obsoletas SOLO si existen ─────────────────
        Schema::table('series_comprobantes', function (Blueprint $table) {
            $oldColumns = ['tipo', 'descripcion', 'correlativo_inicial', 'correlativo_final', 'electronico', 'formato', 'activa'];
            foreach ($oldColumns as $col) {
                if (Schema::hasColumn('series_comprobantes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // ── 5. Agregar índices (si no existen) ─────────────────────────────────
        try {
            Schema::table('series_comprobantes', function (Blueprint $table) {
                $indexes = DB::select("SHOW INDEX FROM series_comprobantes WHERE Key_name = 'sc_sucursal_tipo_idx'");
                if (empty($indexes)) {
                    $table->index(['sucursal_id', 'tipo_comprobante'], 'sc_sucursal_tipo_idx');
                }
            });
        } catch (\Exception $e) {
            // índice ya existe
        }
    }

    public function down(): void
    {
        Schema::table('series_comprobantes', function (Blueprint $table) {
            // Solo agregar columnas antiguas si NO existen
            if (!Schema::hasColumn('series_comprobantes', 'tipo')) {
                $table->string('tipo', 2)->nullable()->after('sucursal_id');
            }
            if (!Schema::hasColumn('series_comprobantes', 'descripcion')) {
                $table->string('descripcion')->nullable()->after('serie');
            }
            if (!Schema::hasColumn('series_comprobantes', 'correlativo_inicial')) {
                $table->integer('correlativo_inicial')->nullable();
            }
            if (!Schema::hasColumn('series_comprobantes', 'correlativo_final')) {
                $table->integer('correlativo_final')->nullable();
            }
            if (!Schema::hasColumn('series_comprobantes', 'electronico')) {
                $table->boolean('electronico')->default(false);
            }
            if (!Schema::hasColumn('series_comprobantes', 'formato')) {
                $table->enum('formato', ['ticket', 'a4'])->default('ticket');
            }
            if (!Schema::hasColumn('series_comprobantes', 'activa')) {
                $table->boolean('activa')->default(true);
            }

            // Eliminar columnas nuevas (solo si existen)
            $newColumns = ['tipo_comprobante', 'tipo_nombre', 'formato_impresion', 'activo'];
            foreach ($newColumns as $col) {
                if (Schema::hasColumn('series_comprobantes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};