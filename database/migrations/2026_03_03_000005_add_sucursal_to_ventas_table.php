<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas', 'sucursal_id')) {
                $table->foreignId('sucursal_id')->nullable()->after('almacen_id')
                      ->constrained('sucursales')->onDelete('set null');
            }
            if (!Schema::hasColumn('ventas', 'serie_comprobante_id')) {
                $table->foreignId('serie_comprobante_id')->nullable()->after('sucursal_id')
                      ->constrained('series_comprobantes')->onDelete('set null');
            }
            if (!Schema::hasColumn('ventas', 'correlativo')) {
                $table->unsignedInteger('correlativo')->nullable()->after('serie_comprobante_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropForeign(['serie_comprobante_id']);
            $table->dropColumn(['sucursal_id', 'serie_comprobante_id', 'correlativo']);
        });
    }
};
