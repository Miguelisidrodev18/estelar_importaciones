<?php
// database/migrations/xxxx_update_detalle_compras_with_modelo_color.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('detalle_compras', function (Blueprint $table) {
            if (!Schema::hasColumn('detalle_compras', 'modelo_id')) {
                $table->foreignId('modelo_id')
                      ->nullable()
                      ->after('producto_id')
                      ->constrained('modelos')
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('detalle_compras', 'color_id')) {
                $table->foreignId('color_id')
                      ->nullable()
                      ->after('modelo_id')
                      ->constrained('colores')
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('detalle_compras', 'codigo_barras')) {
                $table->string('codigo_barras', 50)
                      ->nullable()
                      ->after('subtotal');
            }
        });
    }

    public function down()
    {
        Schema::table('detalle_compras', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_compras', 'codigo_barras')) {
                $table->dropColumn('codigo_barras');
            }
            if (Schema::hasColumn('detalle_compras', 'color_id')) {
                $table->dropForeign(['color_id']);
                $table->dropColumn('color_id');
            }
            if (Schema::hasColumn('detalle_compras', 'modelo_id')) {
                $table->dropForeign(['modelo_id']);
                $table->dropColumn('modelo_id');
            }
        });
    }
};