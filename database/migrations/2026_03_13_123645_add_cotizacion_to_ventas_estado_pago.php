<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ventas MODIFY COLUMN estado_pago ENUM('pendiente','pagado','cancelado','anulado','cotizacion') NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ventas MODIFY COLUMN estado_pago ENUM('pendiente','pagado','cancelado') NOT NULL DEFAULT 'pendiente'");
    }
};
