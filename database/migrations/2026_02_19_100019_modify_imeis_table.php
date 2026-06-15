<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            // 1. Ampliar el ENUM para que acepte los valores destino antes de actualizar
            DB::statement("ALTER TABLE `imeis` MODIFY `estado_imei`
                ENUM('en_stock','vendido','garantia','devuelto','reemplazado','disponible','reservado','dañado')
                NOT NULL DEFAULT 'en_stock'");
            // 2. Mapear valores nuevos que no existen en el ENUM anterior
            DB::table('imeis')->whereIn('estado_imei', ['en_stock', 'devuelto', 'reemplazado'])->update(['estado_imei' => 'disponible']);
            // 3. Renombrar columna y reducir ENUM al original
            DB::statement(
                "ALTER TABLE `imeis` CHANGE `estado_imei` `estado`
                 ENUM('disponible','vendido','reservado','dañado','garantia')
                 NOT NULL DEFAULT 'disponible'"
            );
        }

        // Soltar FK y columna en llamadas separadas
        if (Schema::hasColumn('imeis', 'detalle_compra_id')) {
            Schema::table('imeis', fn($t) => $t->dropForeign(['detalle_compra_id']));
            Schema::table('imeis', fn($t) => $t->dropColumn('detalle_compra_id'));
        }
        if (Schema::hasColumn('imeis', 'producto_id')) {
            Schema::table('imeis', fn($t) => $t->dropForeign(['producto_id']));
            Schema::table('imeis', fn($t) => $t->dropColumn('producto_id'));
        }
    }
};