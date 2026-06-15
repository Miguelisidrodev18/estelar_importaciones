<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Las sucursales son siempre tiendas; corregir datos existentes con tipo='almacen'
        DB::table('sucursales')->where('tipo', 'almacen')->update(['tipo' => 'tienda']);

        // Vincular correctamente el sucursal_id en los almacenes auto-creados que no lo tengan
        // (aquellos cuyo código sigue el patrón ALM-S001 creados por SucursalService)
        $sucursales = DB::table('sucursales')->get();
        foreach ($sucursales as $sucursal) {
            $codigoAlmacen = 'ALM-' . $sucursal->codigo;
            DB::table('almacenes')
                ->where('codigo', $codigoAlmacen)
                ->whereNull('sucursal_id')
                ->update(['sucursal_id' => $sucursal->id, 'tipo' => 'tienda']);
        }
    }

    public function down(): void
    {
        // No hay reversión segura para datos corregidos
    }
};
