<?php
// database/migrations/xxxx_add_detalle_compra_id_to_imeis.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('imeis', function (Blueprint $table) {
            $table->foreignId('detalle_compra_id')
                  ->nullable()
                  ->after('compra_id')
                  ->constrained('detalle_compras')
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        if (Schema::hasColumn('imeis', 'detalle_compra_id')) {
            Schema::table('imeis', fn($t) => $t->dropForeign(['detalle_compra_id']));
            Schema::table('imeis', fn($t) => $t->dropColumn('detalle_compra_id'));
        }
    }
};