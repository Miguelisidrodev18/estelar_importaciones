<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\StockAlmacen;
use App\Models\Imei;
use Illuminate\Support\Facades\DB;

class TrasladoService
{
    public function crearTraslado(array $datos): MovimientoInventario
    {
        return DB::transaction(function () use ($datos) {

            $productoId = $datos['producto_id'];
            $almacenOrigenId = $datos['almacen_id'];
            $almacenDestinoId = $datos['almacen_destino_id'];
            $cantidad = $datos['cantidad'];
            $userId = $datos['user_id'];

            if ($almacenOrigenId == $almacenDestinoId) {
                throw new \Exception('Almacén origen y destino no pueden ser iguales');
            }

            $stockOrigen = StockAlmacen::where('producto_id', $productoId)
                ->where('almacen_id', $almacenOrigenId)
                ->first();

            if (!$stockOrigen || $stockOrigen->cantidad < $cantidad) {
                throw new \Exception('Stock insuficiente en almacén origen');
            }

            $numeroGuia = $this->generarNumeroGuia();

            $stockAnterior = $stockOrigen->cantidad;
            $stockOrigen->decrement('cantidad', $cantidad);

            if (isset($datos['imei_id']) && $datos['imei_id']) {
                Imei::where('id', $datos['imei_id'])
                    ->update(['almacen_id' => null]);
            }

            $movimiento = MovimientoInventario::create([
                'producto_id' => $productoId,
                'almacen_id' => $almacenOrigenId,
                'almacen_destino_id' => $almacenDestinoId,
                'user_id' => $userId,
                'imei_id' => $datos['imei_id'] ?? null,
                'tipo_movimiento' => 'transferencia',
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockOrigen->cantidad,
                'numero_guia' => $numeroGuia,
                'fecha_traslado' => now()->toDateString(),
                'transportista' => $datos['transportista'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null,
                'estado' => 'pendiente',
            ]);

            return $movimiento->fresh('producto', 'almacen', 'almacenDestino');
        });
    }

    public function confirmarRecepcion(int $movimientoId, int $usuarioConfirmaId): MovimientoInventario
    {
        return DB::transaction(function () use ($movimientoId, $usuarioConfirmaId) {

            $movimiento = MovimientoInventario::findOrFail($movimientoId);

            if ($movimiento->estado !== 'pendiente') {
                throw new \Exception('Este traslado ya fue procesado');
            }

            if ($movimiento->tipo_movimiento !== 'transferencia') {
                throw new \Exception('Este movimiento no es una transferencia');
            }

            $stockDestino = StockAlmacen::firstOrCreate(
                [
                    'producto_id' => $movimiento->producto_id,
                    'almacen_id' => $movimiento->almacen_destino_id,
                ],
                ['cantidad' => 0]
            );

            $stockDestino->increment('cantidad', $movimiento->cantidad);

            if ($movimiento->imei_id) {
                Imei::where('id', $movimiento->imei_id)
                    ->update(['almacen_id' => $movimiento->almacen_destino_id]);
            }

            $movimiento->update([
                'estado' => 'confirmado',
                'usuario_confirma_id' => $usuarioConfirmaId,
                'fecha_confirmacion' => now(),
                'fecha_recepcion' => now()->toDateString(),
            ]);

            return $movimiento->fresh();
        });
    }

    private function generarNumeroGuia(): string
    {
        $ultimo = MovimientoInventario::where('tipo_movimiento', 'transferencia')
            ->whereNotNull('numero_guia')
            ->latest('id')
            ->first();

        $numero = $ultimo && $ultimo->numero_guia
            ? (int) substr($ultimo->numero_guia, 3) + 1
            : 1;

        return 'GR-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}
