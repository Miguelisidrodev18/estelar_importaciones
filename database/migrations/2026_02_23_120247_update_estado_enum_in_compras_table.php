<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Primero, actualizar datos existentes
        DB::table('compras')
            ->whereIn('estado', ['pendiente', 'borrador', 'completado'])
            ->update(['estado' => 'registrado']);
        
        // Luego, modificar el ENUM
        DB::statement("ALTER TABLE compras MODIFY estado ENUM('registrado', 'anulado') DEFAULT 'registrado'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE compras MODIFY estado ENUM('borrador', 'pendiente', 'completado', 'anulado', 'registrado') DEFAULT 'pendiente'");
    }
};