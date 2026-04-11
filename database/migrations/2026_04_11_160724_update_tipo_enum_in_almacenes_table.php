<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Paso 1: Ampliar el ENUM para que acepte los nuevos valores junto a los viejos
        \DB::statement("ALTER TABLE almacenes MODIFY COLUMN tipo ENUM('principal','sucursal','tienda','deposito','temporal') NOT NULL DEFAULT 'tienda'");

        // Paso 2: Migrar datos — 'sucursal' pasa a 'tienda' (son puntos de venta físicos)
        \DB::statement("UPDATE almacenes SET tipo = 'tienda' WHERE tipo = 'sucursal'");

        // Paso 3: Eliminar el valor obsoleto 'sucursal' del ENUM
        \DB::statement("ALTER TABLE almacenes MODIFY COLUMN tipo ENUM('principal','tienda','deposito','temporal') NOT NULL DEFAULT 'tienda'");
    }

    public function down(): void
    {
        // Paso 1: Ampliar para aceptar 'sucursal' otra vez
        \DB::statement("ALTER TABLE almacenes MODIFY COLUMN tipo ENUM('principal','sucursal','tienda','deposito','temporal') NOT NULL DEFAULT 'sucursal'");

        // Paso 2: Revertir datos
        \DB::statement("UPDATE almacenes SET tipo = 'sucursal' WHERE tipo IN ('tienda','deposito')");

        // Paso 3: Restaurar ENUM original
        \DB::statement("ALTER TABLE almacenes MODIFY COLUMN tipo ENUM('principal','sucursal','temporal') NOT NULL DEFAULT 'sucursal'");
    }
};
