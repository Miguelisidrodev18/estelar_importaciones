<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto_variantes', function (Blueprint $table) {
            $table->decimal('costo_promedio', 12, 2)->nullable()->default(null)->after('sobreprecio')
                  ->comment('Costo Promedio Ponderado calculado desde historial de compras');
            $table->decimal('ultimo_costo_compra', 12, 2)->nullable()->default(null)->after('costo_promedio')
                  ->comment('Precio unitario de la última compra');
        });
    }

    public function down(): void
    {
        Schema::table('producto_variantes', function (Blueprint $table) {
            $table->dropColumn(['costo_promedio', 'ultimo_costo_compra']);
        });
    }
};
