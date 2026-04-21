<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE cuentas_por_pagar MODIFY COLUMN estado ENUM('pendiente', 'pagado', 'parcial', 'vencido', 'anulado') NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE cuentas_por_pagar MODIFY COLUMN estado ENUM('pendiente', 'pagado', 'parcial', 'vencido') NOT NULL DEFAULT 'pendiente'");
    }
};
