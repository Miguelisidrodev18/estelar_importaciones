<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insertar el rol de Cajero
        DB::table('roles')->insert([
            'nombre' => 'Cajero',
            'descripcion' => 'Encargado de tienda - gestiona cobros y ventas del punto de venta',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->where('nombre', 'Cajero')->delete();
    }
};