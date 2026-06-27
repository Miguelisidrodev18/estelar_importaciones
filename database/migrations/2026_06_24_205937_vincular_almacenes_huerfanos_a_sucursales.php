<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $almacenes = DB::table('almacenes')
            ->whereNull('sucursal_id')
            ->where('estado', 'activo')
            ->get();

        if ($almacenes->isEmpty()) {
            return;
        }

        foreach ($almacenes as $almacen) {
            $esTienda = in_array($almacen->tipo, ['tienda']);

            $ultimoCodigo = DB::table('sucursales')->orderByDesc('id')->value('codigo');
            $numero = $ultimoCodigo ? ((int) substr($ultimoCodigo, 1) + 1) : 1;
            $codigo = 'S' . str_pad($numero, 3, '0', STR_PAD_LEFT);

            $nombre = $almacen->nombre;
            $nombre = preg_replace('/^(Tienda|Almacen|Almacén)\s+/iu', '', $nombre);

            $sucursalId = DB::table('sucursales')->insertGetId([
                'codigo'       => $codigo,
                'nombre'       => $nombre,
                'tipo'         => $esTienda ? 'tienda' : 'almacen',
                'direccion'    => $almacen->direccion,
                'almacen_id'   => $almacen->id,
                'es_principal' => false,
                'estado'       => 'activo',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            DB::table('almacenes')
                ->where('id', $almacen->id)
                ->update(['sucursal_id' => $sucursalId]);

            if ($esTienda) {
                $sufijo = str_pad($numero, 2, '0', STR_PAD_LEFT);

                $series = [
                    ['tipo_comprobante' => '01', 'tipo_nombre' => 'Factura Electrónica',          'serie' => "FA{$sufijo}", 'formato_impresion' => 'A4'],
                    ['tipo_comprobante' => '03', 'tipo_nombre' => 'Boleta de Venta Electrónica',  'serie' => "BA{$sufijo}", 'formato_impresion' => 'ticket'],
                    ['tipo_comprobante' => '07', 'tipo_nombre' => 'Nota de Crédito',              'serie' => "FC{$sufijo}", 'formato_impresion' => 'A4'],
                    ['tipo_comprobante' => '08', 'tipo_nombre' => 'Nota de Débito',               'serie' => "FD{$sufijo}", 'formato_impresion' => 'A4'],
                    ['tipo_comprobante' => '09', 'tipo_nombre' => 'Guía de Remisión Remitente',   'serie' => "T{$sufijo}01", 'formato_impresion' => 'A4'],
                    ['tipo_comprobante' => 'NE', 'tipo_nombre' => 'Nota de Entrega/Cotización',   'serie' => "CO{$sufijo}", 'formato_impresion' => 'A4'],
                ];

                foreach ($series as $s) {
                    $existe = DB::table('series_comprobantes')
                        ->where('sucursal_id', $sucursalId)
                        ->where('tipo_comprobante', $s['tipo_comprobante'])
                        ->exists();

                    if (!$existe) {
                        DB::table('series_comprobantes')->insert([
                            'sucursal_id'        => $sucursalId,
                            'tipo_comprobante'    => $s['tipo_comprobante'],
                            'tipo_nombre'         => $s['tipo_nombre'],
                            'serie'               => $s['serie'],
                            'correlativo_actual'  => 1,
                            'formato_impresion'   => $s['formato_impresion'],
                            'activo'              => true,
                            'created_at'          => now(),
                            'updated_at'          => now(),
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
    }
};
