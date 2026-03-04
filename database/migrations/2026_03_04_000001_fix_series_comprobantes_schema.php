<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Agregar nuevas columnas (nullable inicialmente) ──────────────────
        Schema::table('series_comprobantes', function (Blueprint $table) {
            $table->string('tipo_comprobante', 5)->nullable()->after('sucursal_id');
            $table->string('tipo_nombre', 80)->nullable()->after('tipo_comprobante');
            $table->string('formato_impresion', 10)->nullable()->after('correlativo_actual');
            $table->boolean('activo')->default(true)->after('formato_impresion');
        });

        // ── 2. Migrar datos de columnas viejas a nuevas ─────────────────────────
        DB::statement("
            UPDATE series_comprobantes SET
                tipo_comprobante = tipo,
                tipo_nombre = CASE tipo
                    WHEN '01' THEN 'Factura Electrónica'
                    WHEN '03' THEN 'Boleta de Venta Electrónica'
                    WHEN '07' THEN 'Nota de Crédito'
                    WHEN '08' THEN 'Nota de Débito'
                    WHEN '09' THEN 'Guía de Remisión Remitente'
                    WHEN 'NE' THEN 'Nota de Entrega/Cotización'
                    ELSE COALESCE(descripcion, tipo)
                END,
                formato_impresion = CASE
                    WHEN formato = 'a4' OR formato = 'A4' THEN 'A4'
                    ELSE 'ticket'
                END,
                activo = activa
        ");

        // ── 3. Hacer NOT NULL, ampliar serie a 5 chars ─────────────────────────
        DB::statement("
            ALTER TABLE series_comprobantes
                MODIFY tipo_comprobante VARCHAR(5) NOT NULL,
                MODIFY tipo_nombre      VARCHAR(80) NOT NULL,
                MODIFY formato_impresion VARCHAR(10) NOT NULL DEFAULT 'A4',
                MODIFY serie            VARCHAR(5) NOT NULL
        ");

        // ── 4. Eliminar columnas obsoletas ──────────────────────────────────────
        Schema::table('series_comprobantes', function (Blueprint $table) {
            $oldColumns = [];
            foreach (['tipo', 'descripcion', 'correlativo_inicial', 'correlativo_final', 'electronico', 'formato', 'activa'] as $col) {
                if (Schema::hasColumn('series_comprobantes', $col)) {
                    $oldColumns[] = $col;
                }
            }
            if (!empty($oldColumns)) {
                $table->dropColumn($oldColumns);
            }
        });

        // ── 5. Agregar índices (si no existen) ─────────────────────────────────
        Schema::table('series_comprobantes', function (Blueprint $table) {
            try {
                $table->index(['sucursal_id', 'tipo_comprobante'], 'sc_sucursal_tipo_idx');
            } catch (\Exception $e) {
                // índice ya existe
            }
        });
    }

    public function down(): void
    {
        Schema::table('series_comprobantes', function (Blueprint $table) {
            $table->string('tipo', 2)->nullable()->after('sucursal_id');
            $table->string('descripcion')->nullable()->after('serie');
            $table->integer('correlativo_inicial')->nullable();
            $table->integer('correlativo_final')->nullable();
            $table->boolean('electronico')->default(false);
            $table->enum('formato', ['ticket', 'a4'])->default('ticket');
            $table->boolean('activa')->default(true);
            $table->dropColumn(['tipo_comprobante', 'tipo_nombre', 'formato_impresion', 'activo']);
        });
    }
};
