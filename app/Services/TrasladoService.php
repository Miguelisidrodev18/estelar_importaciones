<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\StockAlmacen;
use App\Models\Imei;
use App\Models\TrasladoImei;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class TrasladoService
{
    public function crearTraslado(array $datos): MovimientoInventario
    {
        return DB::transaction(function () use ($datos) {

            $productoId       = $datos['producto_id'];
            $almacenOrigenId  = $datos['almacen_id'];
            $almacenDestinoId = $datos['almacen_destino_id'];
            $userId           = $datos['user_id'];

            if ($almacenOrigenId == $almacenDestinoId) {
                throw new \Exception('Almacén origen y destino no pueden ser iguales');
            }

            $producto = Producto::findOrFail($productoId);
            $esSerie  = $producto->tipo_inventario === 'serie';

            $numeroGuia = !empty($datos['numero_guia'])
                ? strtoupper(trim($datos['numero_guia']))
                : $this->generarNumeroGuia();

            // ── Producto con IMEI ──────────────────────────────────────────
            if ($esSerie) {
                $imeiIds  = array_values(array_unique((array) $datos['imei_ids']));
                $cantidad = count($imeiIds);

                if ($cantidad === 0) {
                    throw new \Exception('Debe seleccionar al menos un IMEI para trasladar.');
                }

                // Duplicados
                if (count($imeiIds) !== count(array_unique($imeiIds))) {
                    throw new \Exception('Hay IMEIs duplicados en la selección.');
                }

                // Validar que todos pertenezcan al producto + almacén origen + estado en_stock
                $imeisValidos = Imei::whereIn('id', $imeiIds)
                    ->where('producto_id', $productoId)
                    ->where('almacen_id', $almacenOrigenId)
                    ->where('estado_imei', 'en_stock')
                    ->count();

                if ($imeisValidos !== $cantidad) {
                    throw new \Exception(
                        'Algunos IMEIs seleccionados no están disponibles en el almacén origen o no pertenecen al producto.'
                    );
                }

                $stockAnterior = Imei::where('producto_id', $productoId)
                    ->where('almacen_id', $almacenOrigenId)
                    ->where('estado_imei', 'en_stock')
                    ->count();

                $movimiento = MovimientoInventario::create([
                    'producto_id'        => $productoId,
                    'almacen_id'         => $almacenOrigenId,
                    'almacen_destino_id' => $almacenDestinoId,
                    'user_id'            => $userId,
                    'tipo_movimiento'    => 'transferencia',
                    'cantidad'           => $cantidad,
                    'stock_anterior'     => $stockAnterior,
                    'stock_nuevo'        => $stockAnterior - $cantidad,
                    'numero_guia'        => $numeroGuia,
                    'fecha_traslado'     => now()->toDateString(),
                    'transportista'      => $datos['transportista'] ?? null,
                    'observaciones'      => $datos['observaciones'] ?? null,
                    'estado'             => 'pendiente',
                ]);

                // Guardar IMEIs seleccionados en traslado_imeis desde la creación
                foreach ($imeiIds as $imeiId) {
                    TrasladoImei::create([
                        'movimiento_id' => $movimiento->id,
                        'imei_id'       => $imeiId,
                    ]);
                }

            // ── Producto sin IMEI ──────────────────────────────────────────
            } else {
                $cantidad = $datos['cantidad'];

                $stockOrigen = StockAlmacen::where('producto_id', $productoId)
                    ->where('almacen_id', $almacenOrigenId)
                    ->first();

                if (!$stockOrigen || $stockOrigen->cantidad < $cantidad) {
                    throw new \Exception('Stock insuficiente en almacén origen');
                }

                $stockAnterior = $stockOrigen->cantidad;
                $stockOrigen->decrement('cantidad', $cantidad);

                $movimiento = MovimientoInventario::create([
                    'producto_id'        => $productoId,
                    'almacen_id'         => $almacenOrigenId,
                    'almacen_destino_id' => $almacenDestinoId,
                    'user_id'            => $userId,
                    'tipo_movimiento'    => 'transferencia',
                    'cantidad'           => $cantidad,
                    'stock_anterior'     => $stockAnterior,
                    'stock_nuevo'        => $stockOrigen->cantidad,
                    'numero_guia'        => $numeroGuia,
                    'fecha_traslado'     => now()->toDateString(),
                    'transportista'      => $datos['transportista'] ?? null,
                    'observaciones'      => $datos['observaciones'] ?? null,
                    'estado'             => 'pendiente',
                ]);
            }

            return $movimiento->fresh('producto', 'almacen', 'almacenDestino');
        });
    }

    /**
     * Confirmar recepción de un traslado.
     * Para productos serie: usa los IMEIs pre-asignados en traslado_imeis.
     * Para accesorios: actualiza StockAlmacen normalmente.
     */
    public function confirmarRecepcion(int $movimientoId, int $usuarioConfirmaId): MovimientoInventario
    {
        return DB::transaction(function () use ($movimientoId, $usuarioConfirmaId) {

            $movimiento = MovimientoInventario::with('producto')->findOrFail($movimientoId);

            if ($movimiento->estado !== 'pendiente') {
                throw new \Exception('Este traslado ya fue procesado');
            }

            if ($movimiento->tipo_movimiento !== 'transferencia') {
                throw new \Exception('Este movimiento no es una transferencia');
            }

            $esSerie = $movimiento->producto->tipo_inventario === 'serie';

            if ($esSerie) {
                // Usar IMEIs pre-asignados al crear el traslado
                $imeiIds = TrasladoImei::where('movimiento_id', $movimientoId)
                    ->pluck('imei_id')
                    ->toArray();

                if (count($imeiIds) === 0) {
                    throw new \Exception('Este traslado no tiene IMEIs asignados. Contacte al administrador.');
                }

                if (count($imeiIds) !== (int) $movimiento->cantidad) {
                    throw new \Exception(
                        "Inconsistencia: el traslado indica {$movimiento->cantidad} unidad(es) pero tiene " . count($imeiIds) . " IMEI(s) asignado(s)."
                    );
                }

                // Mover IMEIs al almacén destino
                Imei::whereIn('id', $imeiIds)
                    ->update(['almacen_id' => $movimiento->almacen_destino_id]);

            } else {
                // Producto accesorio: manejo de StockAlmacen

                // Si es solicitud de tienda el stock origen aún no fue descontado
                $esSolicitudTienda = (int) $movimiento->stock_nuevo === (int) $movimiento->stock_anterior;

                if ($esSolicitudTienda) {
                    $stockOrigen = StockAlmacen::where([
                        'producto_id' => $movimiento->producto_id,
                        'almacen_id'  => $movimiento->almacen_id,
                    ])->first();

                    if (!$stockOrigen || $stockOrigen->cantidad < $movimiento->cantidad) {
                        throw new \Exception('Stock insuficiente en el almacén origen para confirmar el traslado');
                    }

                    $stockOrigen->decrement('cantidad', $movimiento->cantidad);
                }

                // Acreditar stock en destino
                $stockDestino = StockAlmacen::firstOrCreate(
                    [
                        'producto_id' => $movimiento->producto_id,
                        'almacen_id'  => $movimiento->almacen_destino_id,
                    ],
                    ['cantidad' => 0]
                );
                $stockDestino->increment('cantidad', $movimiento->cantidad);
            }

            $movimiento->update([
                'estado'              => 'confirmado',
                'usuario_confirma_id' => $usuarioConfirmaId,
                'fecha_confirmacion'  => now(),
                'fecha_recepcion'     => now()->toDateString(),
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
