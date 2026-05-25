<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guias_remision', function (Blueprint $table) {
            $table->foreignId('almacen_id')->nullable()->constrained('almacenes')->nullOnDelete()->after('id');
            $table->enum('tipo_destino', ['almacen', 'cliente', 'proveedor', 'libre'])->default('libre')->after('almacen_id');
            $table->foreignId('almacen_destino_id')->nullable()->constrained('almacenes')->nullOnDelete()->after('tipo_destino');
            $table->unsignedBigInteger('guia_serie_id')->nullable()->after('numero_guia');
            $table->foreign('guia_serie_id')->references('id')->on('series_comprobantes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('guias_remision', function (Blueprint $table) {
            $table->dropForeign(['almacen_id']);
            $table->dropForeign(['almacen_destino_id']);
            $table->dropForeign(['guia_serie_id']);
            $table->dropColumn(['almacen_id', 'tipo_destino', 'almacen_destino_id', 'guia_serie_id']);
        });
    }
};
