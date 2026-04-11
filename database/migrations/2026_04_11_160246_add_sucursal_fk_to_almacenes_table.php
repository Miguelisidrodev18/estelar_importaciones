<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('almacenes', function (Blueprint $table) {
            // Añadir la columna si todavía no existe (producción puede no tenerla)
            if (!Schema::hasColumn('almacenes', 'sucursal_id')) {
                $table->unsignedBigInteger('sucursal_id')->nullable()->after('id');
            }
        });

        Schema::table('almacenes', function (Blueprint $table) {
            // Añadir FK solo si no existe ya
            $fks = collect(\DB::select("
                SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'almacenes'
                  AND COLUMN_NAME = 'sucursal_id'
                  AND REFERENCED_TABLE_NAME = 'sucursales'
            "));
            if ($fks->isEmpty()) {
                $table->foreign('sucursal_id')
                      ->references('id')
                      ->on('sucursales')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('almacenes', function (Blueprint $table) {
            if (Schema::hasColumn('almacenes', 'sucursal_id')) {
                $table->dropForeign(['sucursal_id']);
                $table->dropColumn('sucursal_id');
            }
        });
    }
};
