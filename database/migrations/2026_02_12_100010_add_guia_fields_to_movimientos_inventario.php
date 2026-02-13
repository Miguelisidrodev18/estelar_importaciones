<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->string('numero_factura', 50)->nullable()->after('documento_referencia');
            $table->string('numero_guia', 50)->nullable()->after('numero_factura');
            $table->string('transportista')->nullable()->after('numero_guia');
            $table->date('fecha_traslado')->nullable()->after('transportista');
            $table->date('fecha_recepcion')->nullable()->after('fecha_traslado');
            $table->foreignId('usuario_confirma_id')->nullable()->after('user_id')->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_confirmacion')->nullable()->after('usuario_confirma_id');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropForeign(['usuario_confirma_id']);
            $table->dropColumn([
                'numero_factura',
                'numero_guia',
                'transportista',
                'fecha_traslado',
                'fecha_recepcion',
                'usuario_confirma_id',
                'fecha_confirmacion',
            ]);
        });
    }
};
