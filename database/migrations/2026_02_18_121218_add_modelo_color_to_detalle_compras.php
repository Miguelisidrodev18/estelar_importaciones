<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('detalle_compras', function (Blueprint $table) {
            $table->dropForeign(['modelo_id']);
            $table->dropForeign(['color_id']);
            $table->dropColumn(['modelo_id', 'color_id']);
        });
    }
};
