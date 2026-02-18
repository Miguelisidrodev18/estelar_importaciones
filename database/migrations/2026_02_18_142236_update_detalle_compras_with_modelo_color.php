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
            // Agregar nuevas columnas
            $table->foreignId('modelo_id')
                  ->nullable()
                  ->after('producto_id')
                  ->constrained('modelos')
                  ->nullOnDelete();
            
            $table->foreignId('color_id')
                  ->nullable()
                  ->after('modelo_id')
                  ->constrained('colores')
                  ->nullOnDelete();
            
            $table->string('codigo_barras', 50)
                  ->nullable()
                  ->after('subtotal');
        });
    }

    public function down()
    {
        Schema::table('detalle_compras', function (Blueprint $table) {
            $table->dropForeign(['modelo_id']);
            $table->dropForeign(['color_id']);
            $table->dropColumn(['modelo_id', 'color_id', 'codigo_barras']);
        });
    }
};