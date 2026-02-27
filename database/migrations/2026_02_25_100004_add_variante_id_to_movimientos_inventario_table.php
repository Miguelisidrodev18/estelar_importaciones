<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->foreignId('variante_id')
                  ->nullable()
                  ->after('producto_id')
                  ->constrained('producto_variantes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropForeign(['variante_id']);
            $table->dropColumn('variante_id');
        });
    }
};
