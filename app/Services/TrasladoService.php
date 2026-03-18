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
    /**
     * Crear un traslado con múltiples productos.
     *
     * Para productos serie (IMEI): marca IMEIs como en_transito al crear.
     * Para accesorios: registra la intención; el stock se mueve al confirmar.
     *
     * @param array $datos  Incluye: almacen_id, almacen_destino_id, productos[],
     *                      numero_guia?, transportista?, observaciones?, user_id
     * @return string  El numero_guia generado o provisto
     */
    public function crearTraslado(array $datos): string
    {
        return DB::transaction(function () use ($datos) {

            $almacenOrigenId  = $datos['almacen_id'];
            $almacenDestinoId = $datos['almacen_destino_id'];
            $userId           = $datos['user_id'];
            $productos        = $datos['productos'] ?? [];

            if ($almacenOrigenId == $almacenDestinoId) {
                throw new \Exception('Almacén origen y destino no pueden ser iguales.');
            }

            if (empty($productos)) {
                throw new \Exception('Debe incluir al menos un producto en el traslado.');
            }

            // Un único numero_guia para todo el traslado (grupo)
            $numeroGuia = !empty($datos['numero_guia'])
                ? strtoupper(trim($datos['numero_guia']))
                : $this->generarNumeroGuia();

            // Verificar que el numero_guia no esté ya en uso
            if (MovimientoInventario::where('numero_guia', $numeroGuia)->exists()) {
                throw new \Exception("La guía '{$numeroGuia}' ya existe. Deja el campo vacío para auto-generar.");
            }

            // Validar productos duplicados
            $productosIds = array_column($productos, 'producto_id');
            if (count($productosIds) !== count(array_unique($productosIds))) {
                throw new \Exception('Hay productos duplicados en el traslado.');
            }

            foreach ($productos as $linea) {
                $productoId = $linea['producto_id'];
                $producto   = Producto::findOrFail($productoId);
                $esSerie    = $producto->tipo_inventario === 'serie';

                if ($esSerie) {
                    $this->crearLineaImei($linea, $producto, $almacenOrigenId, $almacenDestinoId, $userId, $numeroGuia, $datos);
                } else {
                    $this->crearLineaAccesorio($linea, $producto, $almacenOrigenId, $almacenDestinoId, $userId, $numeroGuia, $datos);
                }
            }

            return $numeroGuia;
        });
    }

    // ── Línea serie (IMEI) ──────────────────────────────────────────────────

    private function crearLineaImei(array $linea, Producto $producto, int $origenId, int $destinoId, int $userId, string $guia, array $cabecera): void
    {
        $imeiIds = array_values(array_unique((array) ($linea['imei_ids'] ?? [])));
        $cantidad = count($imeiIds);

        if ($cantidad === 0) {
            throw new \Exception("Producto «{$producto->nombre}»: debe seleccionar al menos un IMEI.");
        }

        // Validar que todos los IMEIs pertenecen al producto + almacén + en_stock
        $validos = Imei::whereIn('id', $imeiIds)
            ->where('producto_id', $producto->id)
            ->where('almacen_id', $origenId)
            ->where('estado_imei', Imei::ESTADO_EN_STOCK)
            ->count();

        if ($validos !== $cantidad) {
            throw new \Exception(
                "Producto «{$producto->nombre}»: uno o más IMEIs no están disponibles en el almacén origen o ya están en tránsito."
            );
        }

        // Verificar que ningún IMEI esté en otro traslado pendiente (doble seguridad)
        $enOtroTraslado = TrasladoImei::whereIn('imei_id', $imeiIds)
            ->whereHas('movimiento', fn($q) => $q->where('estado', 'pendiente'))
            ->exists();

        if ($enOtroTraslado) {
            throw new \Exception("Producto «{$producto->nombre}»: uno o más IMEIs ya están asignados a otro traslado pendiente.");
        }

        $stockAnterior = Imei::where('producto_id', $producto->id)
            ->where('almacen_id', $origenId)
            ->where('estado_imei', Imei::ESTADO_EN_STOCK)
            ->count();

        $movimiento = MovimientoInventario::create([
            'producto_id'        => $producto->id,
            'almacen_id'         => $origenId,
            'almacen_destino_id' => $destinoId,
            'user_id'            => $userId,
            'tipo_movimiento'    => 'transferencia',
            'cantidad'           => $cantidad,
            'stock_anterior'     => $stockAnterior,
            'stock_nuevo'        => $stockAnterior - $cantidad,
            'numero_guia'        => $guia,
            'fecha_traslado'     => now()->toDateString(),
            'transportista'      => $cabecera['transportista'] ?? null,
            'observaciones'      => $cabecera['observaciones'] ?? null,
            'estado'             => 'pendiente',
        ]);

        // Registrar IMEIs del traslado y marcarlos en_transito
        foreach ($imeiIds as $imeiId) {
            TrasladoImei::create([
                'movimiento_id' => $movimiento->id,
                'imei_id'       => $imeiId,
            ]);
        }

        Imei::whereIn('id', $imeiIds)
            ->update(['estado_imei' => Imei::ESTADO_EN_TRANSITO]);
    }

    // ── Línea accesorio (cantidad) ──────────────────────────────────────────

    private function crearLineaAccesorio(array $linea, Producto $producto, int $origenId, int $destinoId, int $userId, string $guia, array $cabecera): void
    {
        $cantidad = (int) ($linea['cantidad'] ?? 0);

        if ($cantidad < 1) {
            throw new \Exception("Producto «{$producto->nombre}»: la cantidad debe ser al menos 1.");
        }

        $stockOrigen = StockAlmacen::where('producto_id', $producto->id)
            ->where('almacen_id', $origenId)
            ->first();

        if (!$stockOrigen || $stockOrigen->cantidad < $cantidad) {
            throw new \Exception(
                "Producto «{$producto->nombre}»: stock insuficiente en almacén origen (disponible: " . ($stockOrigen->cantidad ?? 0) . ")."
            );
        }

        MovimientoInventario::create([
            'producto_id'        => $producto->id,
            'almacen_id'         => $origenId,
            'almacen_destino_id' => $destinoId,
            'user_id'            => $userId,
            'tipo_movimiento'    => 'transferencia',
            'cantidad'           => $cantidad,
            'stock_anterior'     => $stockOrigen->cantidad,
            'stock_nuevo'        => $stockOrigen->cantidad - $cantidad,  // proyectado
            'numero_guia'        => $guia,
            'fecha_traslado'     => now()->toDateString(),
            'transportista'      => $cabecera['transportista'] ?? null,
            'observaciones'      => $cabecera['observaciones'] ?? null,
            'estado'             => 'pendiente',
        ]);

        // Descontar stock en origen inmediatamente (reserva)
        $stockOrigen->decrement('cantidad', $cantidad);
    }

    // ───────────────────────────────────────────────────────────────────────

    /**
     * Confirmar recepción de un traslado (confirma TODOS los productos del mismo numero_guia).
     */
    public function confirmarRecepcion(int $movimientoId, int $usuarioConfirmaId): void
    {
        DB::transaction(function () use ($movimientoId, $usuarioConfirmaId) {

            $representante = MovimientoInventario::with('producto')->findOrFail($movimientoId);

            if ($representante->estado !== 'pendiente') {
                throw new \Exception('Este traslado ya fue procesado.');
            }

            // Obtener todos los movimientos del mismo grupo (mismo numero_guia)
            $grupo = $representante->numero_guia
                ? MovimientoInventario::with(['producto', 'imeisTrasladados'])
                    ->where('numero_guia', $representante->numero_guia)
                    ->where('tipo_movimiento', 'transferencia')
                    ->where('estado', 'pendiente')
                    ->get()
                : collect([$representante->load('imeisTrasladados')]);

            if ($grupo->isEmpty()) {
                throw new \Exception('No se encontraron movimientos pendientes para este traslado.');
            }

            $confirmadoEn = now();

            foreach ($grupo as $movimiento) {
                $esSerie = $movimiento->producto->tipo_inventario === 'serie';

                if ($esSerie) {
                    // Mover IMEIs al almacén destino y devolverles en_stock
                    $imeiIds = $movimiento->imeisTrasladados->pluck('imei_id')->toArray();

                    if (empty($imeiIds)) {
                        throw new \Exception(
                            "Producto «{$movimiento->producto->nombre}»: no tiene IMEIs asignados. Contacte al administrador."
                        );
                    }

                    Imei::whereIn('id', $imeiIds)->update([
                        'almacen_id'  => $movimiento->almacen_destino_id,
                        'estado_imei' => Imei::ESTADO_EN_STOCK,
                    ]);

                } else {
                    // Acreditar stock en destino (ya fue descontado en origen al crear)
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
                    'fecha_confirmacion'  => $confirmadoEn,
                    'fecha_recepcion'     => $confirmadoEn->toDateString(),
                ]);
            }
        });
    }

    // ───────────────────────────────────────────────────────────────────────

    private function generarNumeroGuia(): string
    {
        $ultimo = MovimientoInventario::where('tipo_movimiento', 'transferencia')
            ->whereNotNull('numero_guia')
            ->latest('id')
            ->value('numero_guia');

        $numero = $ultimo ? ((int) substr($ultimo, 3) + 1) : 1;

        return 'GR-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}
