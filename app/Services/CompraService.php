<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\StockAlmacen;
use App\Models\MovimientoInventario;
use App\Models\Imei;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class CompraService
{
    public function registrarCompra(array $datosCompra, array $detalles): Compra
    {
        return DB::transaction(function () use ($datosCompra, $detalles) {

            $compra = Compra::create($datosCompra);

            foreach ($detalles as $detalle) {
                $producto = Producto::findOrFail($detalle['producto_id']);

                DetalleCompra::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario'],
                ]);

                $stock = StockAlmacen::firstOrCreate(
                    [
                        'producto_id' => $detalle['producto_id'],
                        'almacen_id' => $compra->almacen_id,
                    ],
                    ['cantidad' => 0]
                );

                $stockAnterior = $stock->cantidad;
                $stock->increment('cantidad', $detalle['cantidad']);

                MovimientoInventario::create([
                    'producto_id' => $detalle['producto_id'],
                    'almacen_id' => $compra->almacen_id,
                    'user_id' => $compra->user_id,
                    'tipo_movimiento' => 'ingreso',
                    'cantidad' => $detalle['cantidad'],
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stock->cantidad,
                    'numero_factura' => $compra->numero_factura,
                    'documento_referencia' => $compra->codigo,
                    'motivo' => 'Compra a proveedor',
                    'estado' => 'completado',
                ]);

                if ($producto->tipo_producto === 'celular' && isset($detalle['imeis']) && is_array($detalle['imeis'])) {
                    foreach ($detalle['imeis'] as $imeiData) {
                        Imei::create([
                            'codigo_imei' => $imeiData['codigo_imei'],
                            'serie' => $imeiData['serie'] ?? null,
                            'color' => $imeiData['color'] ?? null,
                            'producto_id' => $detalle['producto_id'],
                            'almacen_id' => $compra->almacen_id,
                            'compra_id' => $compra->id,
                            'estado' => 'disponible',
                        ]);
                    }
                }

                $producto->update([
                    'precio_compra_actual' => $detalle['precio_unitario'],
                ]);
            }

            return $compra->fresh('detalles.producto', 'proveedor', 'almacen');
        });
    }
}
