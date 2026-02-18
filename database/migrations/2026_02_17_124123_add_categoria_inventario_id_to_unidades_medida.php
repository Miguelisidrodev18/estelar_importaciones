<?php
// database/migrations/xxxx_add_categoria_inventario_id_to_unidades_medida.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('unidades_medida', function (Blueprint $table) {
            $table->foreignId('categoria_inventario_id')
                  ->nullable()
                  ->after('categoria')
                  ->constrained('categorias')
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('unidades_medida', function (Blueprint $table) {
            $table->dropForeign(['categoria_inventario_id']);
            $table->dropColumn('categoria_inventario_id');
        });
    }
};