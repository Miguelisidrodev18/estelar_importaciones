<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('detalle_compras', function (Blueprint $table) {
            $table->decimal('costo_prorateado_pen', 12, 4)->default(0)->after('subtotal');
            $table->decimal('costo_unitario_final_pen', 12, 4)->default(0)->after('costo_prorateado_pen');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_compras', function (Blueprint $table) {
            $table->dropColumn(['costo_prorateado_pen', 'costo_unitario_final_pen']);
        });
    }
};
