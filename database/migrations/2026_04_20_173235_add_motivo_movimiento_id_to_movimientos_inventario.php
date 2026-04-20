<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->foreignId('motivo_movimiento_id')
                  ->nullable()
                  ->after('motivo')
                  ->constrained('motivos_movimiento')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropForeign(['motivo_movimiento_id']);
            $table->dropColumn('motivo_movimiento_id');
        });
    }
};
