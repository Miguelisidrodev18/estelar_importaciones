<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add porcentaje_margen to tipo_calculo enum in comision_reglas
        DB::statement("ALTER TABLE comision_reglas MODIFY tipo_calculo ENUM('porcentaje','monto_fijo','porcentaje_margen') NOT NULL");

        // Add margen_calculado snapshot to comision_detalle_venta
        Schema::table('comision_detalle_venta', function (Blueprint $table) {
            $table->decimal('margen_calculado', 10, 2)->nullable()->after('valor_configurado')
                ->comment('Margen (precio - costo) snapshot cuando tipo_calculo=porcentaje_margen');
        });
    }

    public function down(): void
    {
        Schema::table('comision_detalle_venta', function (Blueprint $table) {
            $table->dropColumn('margen_calculado');
        });

        DB::statement("ALTER TABLE comision_reglas MODIFY tipo_calculo ENUM('porcentaje','monto_fijo') NOT NULL");
    }
};
