<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_compras', function (Blueprint $table) {
            $table->foreignId('modelo_id')->nullable()->after('producto_id')
                  ->constrained('modelos')->nullOnDelete();

            $table->foreignId('color_id')->nullable()->after('modelo_id')
                  ->constrained('colores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $fks = array_column(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='detalle_compras' AND CONSTRAINT_TYPE='FOREIGN KEY'"), 'CONSTRAINT_NAME');

        if (in_array('detalle_compras_modelo_id_foreign', $fks)) {
            Schema::table('detalle_compras', fn($t) => $t->dropForeign(['modelo_id']));
        }
        if (in_array('detalle_compras_color_id_foreign', $fks)) {
            Schema::table('detalle_compras', fn($t) => $t->dropForeign(['color_id']));
        }

        Schema::table('detalle_compras', function (Blueprint $table) {
            $cols = array_filter(['modelo_id', 'color_id'], fn($c) => Schema::hasColumn('detalle_compras', $c));
            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
