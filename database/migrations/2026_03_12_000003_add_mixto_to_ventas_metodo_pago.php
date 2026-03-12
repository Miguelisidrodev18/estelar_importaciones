<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ventas MODIFY COLUMN metodo_pago ENUM('efectivo','transferencia','yape','plin','mixto') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ventas MODIFY COLUMN metodo_pago ENUM('efectivo','transferencia','yape','plin') NULL");
    }
};
