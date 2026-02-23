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
        // Usar ALTER TABLE directo porque Laravel no soporta cambiar ENUMs limpiamente
        DB::statement("ALTER TABLE compras MODIFY COLUMN estado ENUM('borrador','pendiente','completado','anulado','registrado') NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE compras MODIFY COLUMN estado ENUM('registrado','anulado') NOT NULL DEFAULT 'registrado'");
    }
};
