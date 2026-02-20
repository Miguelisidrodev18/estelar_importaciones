<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\StockAlmacen;
use App\Models\MovimientoInventario;
use App\Models\Imei;
use App\Models\Caja;
use Illuminate\Support\Facades\DB;

class VentaService
{
    public function crearVenta(array $datosVenta, array $detalles): Venta
    {
        return DB::transaction(function () use ($datosVenta, $detalles) {

            foreach ($detalles as $detalle) {
                $stock = StockAlmacen::where('producto_id', $detalle['producto_id'])
                    ->where('almacen_id', $datosVenta['almacen_id'])
                    ->first();

                if (!$stock || $stock->cantidad < $detalle['cantidad']) {
                    throw new \Exception("Stock insuficiente para producto ID {$detalle['producto_id']}");
                }

                if (isset($detalle['imei_id']) && $detalle['imei_id']) {
                    $imei = Imei::find($detalle['imei_id']);
                    if (!$imei || $imei->estado_imei !== 'en_stock') {
                        throw new \Exception("IMEI no disponible: {$detalle['imei_id']}");
                    }
                }
            }

            $venta = Venta::create(array_merge($datosVenta, [
                'estado_pago' => 'pendiente',
            ]));

            foreach ($detalles as $detalle) {
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $detalle['producto_id'],
                    'imei_id' => $detalle['imei_id'] ?? null,
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario'],
                ]);

                if (isset($detalle['imei_id']) && $detalle['imei_id']) {
                    Imei::where('id', $detalle['imei_id'])
                        ->update(['estado_imei' => 'reservado']);
                }
            }

            return $venta->fresh('detalles.producto', 'cliente');
        });
    }

    public function confirmarPago(int $ventaId, string $metodoPago, int $usuarioConfirmaId): Venta
    {
        return DB::transaction(function () use ($ventaId, $metodoPago, $usuarioConfirmaId) {

            $venta = Venta::with('detalles')->findOrFail($ventaId);

            if ($venta->estado_pago !== 'pendiente') {
                throw new \Exception('Esta venta ya fue procesada.');
            }

            $venta->update([
                'estado_pago' => 'pagado',
                'metodo_pago' => $metodoPago,
                'usuario_confirma_id' => $usuarioConfirmaId,
                'fecha_confirmacion' => now(),
            ]);

            foreach ($venta->detalles as $detalle) {
                $stock = StockAlmacen::where('producto_id', $detalle->producto_id)
                    ->where('almacen_id', $venta->almacen_id)
                    ->firstOrFail();

                $stockAnterior = $stock->cantidad;
                $stock->decrement('cantidad', $detalle->cantidad);

                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_id,
                    'almacen_id' => $venta->almacen_id,
                    'user_id' => $venta->user_id,
                    'imei_id' => $detalle->imei_id,
                    'tipo_movimiento' => 'salida',
                    'cantidad' => $detalle->cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stock->cantidad,
                    'documento_referencia' => $venta->codigo,
                    'motivo' => 'Venta a cliente',
                    'estado' => 'completado',
                ]);

                if ($detalle->imei_id) {
                    Imei::where('id', $detalle->imei_id)
                        ->update(['estado_imei' => 'vendido']);
                }
            }

            $caja = Caja::where('user_id', $usuarioConfirmaId)
                ->where('almacen_id', $venta->almacen_id)
                ->where('estado', 'abierta')
                ->first();

            if ($caja) {
                app(CajaService::class)->registrarMovimiento(
                    $caja->id,
                    'ingreso',
                    $venta->total,
                    "Venta {$venta->codigo}",
                    $venta->id
                );
            }

            return $venta->fresh();
        });
    }
}
