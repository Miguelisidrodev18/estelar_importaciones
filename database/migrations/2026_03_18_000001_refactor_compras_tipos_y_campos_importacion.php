<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * MIGRACIÓN SEGURA — Refactor módulo de Compras
 *
 * Cambios:
 *  1. Migra registros tipo_compra='nacional' → 'local'
 *  2. Agrega columnas USD nuevas (flete_usd, seguro_usd, otros_usd) copiando datos de las viejas
 *  3. Agrega nuevos campos de importación (agente_aduanas, transporte_local_pen, impuestos_usd, impuestos_pen, percepcion_pen)
 *  4. Modifica el enum tipo_compra para solo aceptar 'local' e 'importacion'
 *
 * NOTA: Las columnas antiguas (flete, seguro, otros_gastos) se mantienen para
 * preservar datos históricos. Pueden eliminarse manualmente después de verificar.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── PASO 1: Agregar nuevas columnas (solo si no existen) ────────────────
        Schema::table('compras', function (Blueprint $table) {
            if (!Schema::hasColumn('compras', 'agente_aduanas')) {
                $table->string('agente_aduanas', 255)->nullable()->after('numero_manifiesto');
            }
            if (!Schema::hasColumn('compras', 'flete_usd')) {
                $table->decimal('flete_usd', 10, 2)->default(0)->after('agente_aduanas');
            }
            if (!Schema::hasColumn('compras', 'seguro_usd')) {
                $table->decimal('seguro_usd', 10, 2)->default(0)->after('flete_usd');
            }
            if (!Schema::hasColumn('compras', 'otros_usd')) {
                $table->decimal('otros_usd', 10, 2)->default(0)->after('seguro_usd');
            }
            if (!Schema::hasColumn('compras', 'transporte_local_pen')) {
                $table->decimal('transporte_local_pen', 10, 2)->default(0)->after('otros_usd');
            }
            if (!Schema::hasColumn('compras', 'impuestos_usd')) {
                $table->decimal('impuestos_usd', 10, 2)->default(0)->after('transporte_local_pen');
            }
            if (!Schema::hasColumn('compras', 'impuestos_pen')) {
                $table->decimal('impuestos_pen', 10, 2)->default(0)->after('impuestos_usd');
            }
            if (!Schema::hasColumn('compras', 'percepcion_pen')) {
                $table->decimal('percepcion_pen', 10, 2)->default(0)->after('impuestos_pen');
            }
        });

        // ── PASO 2: Copiar datos de columnas antiguas a las nuevas (solo donde la nueva está vacía) ──
        if (Schema::hasColumn('compras', 'flete') && Schema::hasColumn('compras', 'flete_usd')) {
            DB::statement("UPDATE compras SET flete_usd = flete WHERE flete_usd = 0 AND flete > 0");
        }
        if (Schema::hasColumn('compras', 'seguro') && Schema::hasColumn('compras', 'seguro_usd')) {
            DB::statement("UPDATE compras SET seguro_usd = seguro WHERE seguro_usd = 0 AND seguro > 0");
        }
        if (Schema::hasColumn('compras', 'otros_gastos') && Schema::hasColumn('compras', 'otros_usd')) {
            DB::statement("UPDATE compras SET otros_usd = otros_gastos WHERE otros_usd = 0 AND otros_gastos > 0");
        }

        // ── PASO 3: Migrar 'nacional' → 'local' (ANTES de cambiar el enum) ──────
        DB::table('compras')->where('tipo_compra', 'nacional')->update(['tipo_compra' => 'local']);

        // ── PASO 4: Cambiar el enum para eliminar 'nacional' ────────────────────
        // Se usa DB::statement porque Blueprint::enum() no permite modificar enums directamente
        DB::statement("ALTER TABLE compras MODIFY COLUMN tipo_compra ENUM('local', 'importacion') NOT NULL DEFAULT 'local'");
    }

    public function down(): void
    {
        // Restaurar el enum con 'nacional'
        DB::statement("ALTER TABLE compras MODIFY COLUMN tipo_compra ENUM('local', 'nacional', 'importacion') NOT NULL DEFAULT 'local'");

        // Eliminar columnas nuevas (solo si existen)
        Schema::table('compras', function (Blueprint $table) {
            $nuevas = [
                'agente_aduanas', 'flete_usd', 'seguro_usd', 'otros_usd',
                'transporte_local_pen', 'impuestos_usd', 'impuestos_pen', 'percepcion_pen',
            ];
            foreach ($nuevas as $col) {
                if (Schema::hasColumn('compras', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
