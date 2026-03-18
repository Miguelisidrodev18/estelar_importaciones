<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Solo modificar si en_transito no existe ya en el enum
        $col = DB::select("SHOW COLUMNS FROM imeis LIKE 'estado_imei'");
        if (!empty($col) && !str_contains($col[0]->Type ?? '', 'en_transito')) {
            DB::statement("ALTER TABLE imeis MODIFY COLUMN estado_imei
                ENUM('en_stock','en_transito','reservado','vendido','garantia','devuelto','reemplazado')
                NOT NULL DEFAULT 'en_stock'");
        }
    }

    public function down(): void
    {
        // Revertir IMEIs en_transito a en_stock antes de quitar el estado
        DB::table('imeis')->where('estado_imei', 'en_transito')->update(['estado_imei' => 'en_stock']);

        DB::statement("ALTER TABLE imeis MODIFY COLUMN estado_imei
            ENUM('en_stock','reservado','vendido','garantia','devuelto','reemplazado')
            NOT NULL DEFAULT 'en_stock'");
    }
};
