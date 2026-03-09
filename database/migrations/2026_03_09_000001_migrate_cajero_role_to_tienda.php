<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migrar usuarios con rol 'Cajero' al rol 'Tienda'.
     * El rol 'Cajero' fue renombrado a 'Tienda' en el código pero
     * algunos usuarios pueden seguir teniendo el rol antiguo asignado.
     */
    public function up(): void
    {
        // Obtener IDs de roles
        $cajeroRole = DB::table('roles')->where('nombre', 'Cajero')->first();
        $tiendaRole = DB::table('roles')->where('nombre', 'Tienda')->first();

        if (!$cajeroRole) {
            // No existe el rol Cajero, nada que hacer
            return;
        }

        if (!$tiendaRole) {
            // Si no existe Tienda, renombrar Cajero → Tienda
            DB::table('roles')
                ->where('id', $cajeroRole->id)
                ->update([
                    'nombre'      => 'Tienda',
                    'descripcion' => 'Encargado de tienda - gestiona cobros y ventas del punto de venta.',
                    'updated_at'  => now(),
                ]);
            return;
        }

        // Ambos existen: mover usuarios de Cajero a Tienda
        DB::table('users')
            ->where('role_id', $cajeroRole->id)
            ->update(['role_id' => $tiendaRole->id]);

        // Eliminar el rol Cajero (ya no se usa)
        DB::table('roles')->where('id', $cajeroRole->id)->delete();
    }

    public function down(): void
    {
        // Recrear el rol Cajero si se revierte
        DB::table('roles')->insertOrIgnore([
            'nombre'      => 'Cajero',
            'descripcion' => 'Encargado de tienda - gestiona cobros y ventas del punto de venta',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
};
