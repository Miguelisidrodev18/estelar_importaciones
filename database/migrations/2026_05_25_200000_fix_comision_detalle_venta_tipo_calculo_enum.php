<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE comision_detalle_venta MODIFY tipo_calculo ENUM('porcentaje','monto_fijo','porcentaje_margen') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE comision_detalle_venta MODIFY tipo_calculo ENUM('porcentaje','monto_fijo') NOT NULL");
    }
};
