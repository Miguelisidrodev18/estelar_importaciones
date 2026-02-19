<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Paso 1: Reemplazar 'estado' (valores viejos) por 'estado_imei' (valores nuevos).
        // Se hace drop+add porque CHANGE falla si hay filas con valores del enum antiguo.
        if (Schema::hasColumn('imeis', 'estado') && !Schema::hasColumn('imeis', 'estado_imei')) {
            Schema::table('imeis', function (Blueprint $table) {
                $table->dropColumn('estado');
            });
        }
        if (!Schema::hasColumn('imeis', 'estado_imei')) {
            Schema::table('imeis', function (Blueprint $table) {
                $table->enum('estado_imei', ['en_stock', 'vendido', 'garantia', 'devuelto', 'reemplazado'])
                      ->default('en_stock')
                      ->after('almacen_id');
            });
        }

        // Paso 2: Agregar FKs faltantes
        Schema::table('imeis', function (Blueprint $table) {
            if (!Schema::hasColumn('imeis', 'producto_id')) {
                $table->foreignId('producto_id')->after('id')->constrained('productos');
            }

            if (!Schema::hasColumn('imeis', 'detalle_compra_id')) {
                $table->foreignId('detalle_compra_id')
                      ->nullable()
                      ->constrained('detalle_compras')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Revertir renombrado: 'estado_imei' → 'estado' con enum original
        if (Schema::hasColumn('imeis', 'estado_imei') && !Schema::hasColumn('imeis', 'estado')) {
            \DB::statement(
                "ALTER TABLE `imeis` CHANGE `estado_imei` `estado`
                 ENUM('disponible','vendido','reservado','dañado','garantia')
                 NOT NULL DEFAULT 'disponible'"
            );
        }

        Schema::table('imeis', function (Blueprint $table) {
            if (Schema::hasColumn('imeis', 'detalle_compra_id')) {
                $table->dropForeign(['detalle_compra_id']);
                $table->dropColumn('detalle_compra_id');
            }
            if (Schema::hasColumn('imeis', 'producto_id')) {
                $table->dropForeign(['producto_id']);
                $table->dropColumn('producto_id');
            }
        });
    }
};