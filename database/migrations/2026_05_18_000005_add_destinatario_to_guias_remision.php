<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guias_remision', function (Blueprint $table) {
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete()->after('venta_id');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete()->after('proveedor_id');
        });
    }

    public function down(): void
    {
        Schema::table('guias_remision', function (Blueprint $table) {
            $table->dropForeign(['proveedor_id']);
            $table->dropForeign(['cliente_id']);
        });
        Schema::table('guias_remision', function (Blueprint $table) {
            $table->dropColumn(['proveedor_id', 'cliente_id']);
        });
    }
};
