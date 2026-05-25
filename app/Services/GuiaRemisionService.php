<?php

namespace App\Services;

use App\Models\GuiaRemision;
use App\Models\GuiaRemisionDetalle;
use App\Models\MovimientoInventario;
use App\Models\StockAlmacen;
use App\Models\Imei;
use App\Models\TrasladoImei;
use App\Models\Producto;
use App\Models\SerieComprobante;
use Illuminate\Support\Facades\DB;

class GuiaRemisionService
{
    public function crear(array $datos): GuiaRemision
    {
        return DB::transaction(function () use ($datos) {

            $numeroGuia = $this->resolverNumeroGuia($datos);

            if (MovimientoInventario::where('numero_guia', $numeroGuia)->exists() ||
                GuiaRemision::where('numero_guia', $numeroGuia)->where('id', '!=', 0)->exists()) {
                throw new \Exception("El número de guía '{$numeroGuia}' ya está en uso.");
            }

            $guia = GuiaRemision::create([
                'almacen_id'             => $datos['almacen_id'],
                'tipo_destino'           => $datos['tipo_destino'],
                'almacen_destino_id'     => $datos['tipo_destino'] === 'almacen' ? ($datos['almacen_destino_id'] ?? null) : null,
                'cliente_id'             => $datos['tipo_destino'] === 'cliente'   ? ($datos['cliente_id']   ?? null) : null,
                'proveedor_id'           => $datos['tipo_destino'] === 'proveedor' ? ($datos['proveedor_id'] ?? null) : null,
                'numero_guia'            => $numeroGuia,
                'guia_serie_id'          => $datos['guia_serie_id'] ?? null,
                'motivo_traslado'        => $datos['motivo_traslado'],
                'modalidad'              => $datos['modalidad'],
                'fecha_traslado'         => $datos['fecha_traslado'],
                'peso_total'             => $datos['peso_total']    ?? null,
                'bultos'                 => $datos['bultos']         ?? null,
                'direccion_partida'      => $datos['direccion_partida']  ?? null,
                'ubigeo_partida'         => $datos['ubigeo_partida']     ?? null,
                'direccion_llegada'      => $datos['direccion_llegada']  ?? null,
                'ubigeo_llegada'         => $datos['ubigeo_llegada']     ?? null,
                'transportista_tipo_doc' => $datos['transportista_tipo_doc'] ?? null,
                'transportista_doc'      => $datos['transportista_doc']      ?? null,
                'transportista_nombre'   => $datos['transportista_nombre']   ?? null,
                'conductor_dni'          => $datos['conductor_dni']          ?? null,
                'conductor_nombre'       => $datos['conductor_nombre']       ?? null,
                'conductor_licencia'     => $datos['conductor_licencia']     ?? null,
                'placa_vehiculo'         => $datos['placa_vehiculo']         ?? null,
                'estado'                 => 'pendiente',
            ]);

            foreach ($datos['productos'] as $linea) {
                GuiaRemisionDetalle::create([
                    'guia_remision_id' => $guia->id,
                    'producto_id'      => $linea['producto_id'],
                    'variante_id'      => $linea['variante_id'] ?? null,
                    'cantidad'         => $linea['cantidad'],
                    'descripcion'      => $linea['descripcion'] ?? null,
                ]);

                $this->moverStock($guia, $linea, $datos['user_id'], $numeroGuia);
            }

            if (!empty($datos['guia_serie_id'])) {
                SerieComprobante::where('id', $datos['guia_serie_id'])->increment('correlativo_actual');
            }

            return $guia;
        });
    }

    /**
     * Crea una GuiaRemision vinculada a un traslado ya registrado.
     * No mueve stock (ya lo hizo TrasladoService). Auto-popula los detalles
     * desde los movimientos de inventario existentes con ese numero_guia.
     */
    public function crearParaTraslado(string $numeroGuia, array $datos): GuiaRemision
    {
        return DB::transaction(function () use ($numeroGuia, $datos) {
            if (GuiaRemision::where('numero_guia', $numeroGuia)->exists()) {
                throw new \Exception("Ya existe una guía de remisión para el traslado '{$numeroGuia}'.");
            }

            $guia = GuiaRemision::create([
                'almacen_id'             => $datos['almacen_id'],
                'tipo_destino'           => 'almacen',
                'almacen_destino_id'     => $datos['almacen_destino_id'] ?? null,
                'numero_guia'            => $numeroGuia,
                'guia_serie_id'          => $datos['guia_serie_id'] ?? null,
                'motivo_traslado'        => $datos['motivo_traslado'],
                'modalidad'              => $datos['modalidad'],
                'fecha_traslado'         => $datos['fecha_traslado'],
                'peso_total'             => $datos['peso_total']    ?? null,
                'bultos'                 => $datos['bultos']         ?? null,
                'direccion_partida'      => $datos['direccion_partida']      ?? null,
                'ubigeo_partida'         => $datos['ubigeo_partida']         ?? null,
                'direccion_llegada'      => $datos['direccion_llegada']      ?? null,
                'ubigeo_llegada'         => $datos['ubigeo_llegada']         ?? null,
                'transportista_tipo_doc' => $datos['transportista_tipo_doc'] ?? null,
                'transportista_doc'      => $datos['transportista_doc']      ?? null,
                'transportista_nombre'   => $datos['transportista_nombre']   ?? null,
                'conductor_dni'          => $datos['conductor_dni']          ?? null,
                'conductor_nombre'       => $datos['conductor_nombre']       ?? null,
                'conductor_licencia'     => $datos['conductor_licencia']     ?? null,
                'placa_vehiculo'         => $datos['placa_vehiculo']         ?? null,
                'estado'                 => 'pendiente',
            ]);

            // Auto-poblar detalles desde los movimientos ya existentes
            $movimientos = MovimientoInventario::where('numero_guia', $numeroGuia)
                ->where('tipo_movimiento', 'transferencia')
                ->get();

            foreach ($movimientos as $mov) {
                GuiaRemisionDetalle::create([
                    'guia_remision_id' => $guia->id,
                    'producto_id'      => $mov->producto_id,
                    'variante_id'      => $mov->variante_id,
                    'cantidad'         => $mov->cantidad,
                    'descripcion'      => null,
                ]);
            }

            if (!empty($datos['guia_serie_id'])) {
                SerieComprobante::where('id', $datos['guia_serie_id'])->increment('correlativo_actual');
            }

            return $guia;
        });
    }

    public function confirmarEntrega(GuiaRemision $guia, int $userId): void
    {
        DB::transaction(function () use ($guia, $userId) {
            if (!$guia->puedeConfirmar()) {
                throw new \Exception('Esta guía no puede confirmarse en su estado actual.');
            }

            $movimientos = MovimientoInventario::with(['producto', 'imeisTrasladados'])
                ->where('numero_guia', $guia->numero_guia)
                ->where('estado', 'pendiente')
                ->get();

            foreach ($movimientos as $mov) {
                if ($guia->tipo_destino === 'almacen') {
                    // Transferencia interna: acreditar stock en destino
                    $esSerie = $mov->producto->tipo_inventario === 'serie';

                    if ($esSerie) {
                        $imeiIds = $mov->imeisTrasladados->pluck('imei_id')->toArray();
                        Imei::whereIn('id', $imeiIds)->update([
                            'almacen_id'  => $guia->almacen_destino_id,
                            'estado_imei' => Imei::ESTADO_EN_STOCK,
                        ]);
                    } else {
                        $stockDestino = StockAlmacen::firstOrCreate(
                            ['producto_id' => $mov->producto_id, 'almacen_id' => $guia->almacen_destino_id],
                            ['cantidad' => 0]
                        );
                        $stockDestino->increment('cantidad', $mov->cantidad);
                    }

                    $totalStock = $this->calcularStockTotal($mov->producto_id, $esSerie, $guia->almacen_destino_id);
                    $mov->producto->update(['stock_actual' => $totalStock]);

                } else {
                    // Salida a externo: confirmar IMEIs como salida definitiva
                    $imeiIds = $mov->imeisTrasladados->pluck('imei_id')->toArray();
                    if (!empty($imeiIds)) {
                        Imei::whereIn('id', $imeiIds)->update(['estado_imei' => 'vendido']);
                    }
                }

                $mov->update([
                    'estado'             => 'confirmado',
                    'usuario_confirma_id'=> $userId,
                    'fecha_confirmacion' => now(),
                    'fecha_recepcion'    => now()->toDateString(),
                ]);
            }

            $guia->update(['estado' => 'entregada']);
        });
    }

    public function anular(GuiaRemision $guia, string $motivo, int $userId): void
    {
        DB::transaction(function () use ($guia, $motivo, $userId) {
            if (!$guia->puedeAnular()) {
                throw new \Exception('Esta guía no puede anularse en su estado actual.');
            }

            $movimientos = MovimientoInventario::with(['producto', 'imeisTrasladados'])
                ->where('numero_guia', $guia->numero_guia)
                ->whereIn('estado', ['pendiente', 'confirmado'])
                ->get();

            foreach ($movimientos as $mov) {
                $esSerie = $mov->producto->tipo_inventario === 'serie';

                if ($esSerie) {
                    $imeiIds = $mov->imeisTrasladados->pluck('imei_id')->toArray();
                    if (!empty($imeiIds)) {
                        Imei::whereIn('id', $imeiIds)->update([
                            'almacen_id'  => $guia->almacen_id,
                            'estado_imei' => Imei::ESTADO_EN_STOCK,
                        ]);
                    }
                    $totalStock = Imei::where('producto_id', $mov->producto_id)
                        ->where('estado_imei', Imei::ESTADO_EN_STOCK)->count();
                    $mov->producto->update(['stock_actual' => $totalStock]);
                } else {
                    // Revertir descuento en origen
                    $stockOrigen = StockAlmacen::firstOrCreate(
                        ['producto_id' => $mov->producto_id, 'almacen_id' => $guia->almacen_id],
                        ['cantidad' => 0]
                    );
                    $stockOrigen->increment('cantidad', $mov->cantidad);

                    $totalStock = StockAlmacen::where('producto_id', $mov->producto_id)->sum('cantidad');
                    $mov->producto->update(['stock_actual' => $totalStock]);
                }

                $mov->update(['estado' => 'anulado']);
            }

            $guia->update(['estado' => 'anulada']);
        });
    }

    // ── Privados ─────────────────────────────────────────────────────────────

    private function moverStock(GuiaRemision $guia, array $linea, int $userId, string $numeroGuia): void
    {
        $producto  = Producto::findOrFail($linea['producto_id']);
        $esSerie   = $producto->tipo_inventario === 'serie';
        $almacenId = $guia->almacen_id;
        $tipoMov   = $guia->tipo_destino === 'almacen' ? 'transferencia' : 'salida';

        if ($esSerie) {
            $imeiIds  = array_values(array_unique((array) ($linea['imei_ids'] ?? [])));
            $cantidad = count($imeiIds);

            if ($cantidad === 0) {
                throw new \Exception("Producto «{$producto->nombre}»: debe seleccionar al menos un IMEI.");
            }

            $validos = Imei::whereIn('id', $imeiIds)
                ->where('producto_id', $producto->id)
                ->where('almacen_id', $almacenId)
                ->where('estado_imei', Imei::ESTADO_EN_STOCK)
                ->count();

            if ($validos !== $cantidad) {
                throw new \Exception("Producto «{$producto->nombre}»: uno o más IMEIs no están disponibles.");
            }

            $stockAnterior = Imei::where('producto_id', $producto->id)
                ->where('almacen_id', $almacenId)
                ->where('estado_imei', Imei::ESTADO_EN_STOCK)
                ->count();

            $movimiento = MovimientoInventario::create([
                'producto_id'        => $producto->id,
                'almacen_id'         => $almacenId,
                'almacen_destino_id' => $guia->almacen_destino_id,
                'user_id'            => $userId,
                'tipo_movimiento'    => $tipoMov,
                'cantidad'           => $cantidad,
                'variante_id'        => $linea['variante_id'] ?? null,
                'stock_anterior'     => $stockAnterior,
                'stock_nuevo'        => $stockAnterior - $cantidad,
                'numero_guia'        => $numeroGuia,
                'fecha_traslado'     => $guia->fecha_traslado->toDateString(),
                'observaciones'      => $linea['descripcion'] ?? null,
                'estado'             => 'pendiente',
            ]);

            foreach ($imeiIds as $imeiId) {
                TrasladoImei::create(['movimiento_id' => $movimiento->id, 'imei_id' => $imeiId]);
            }

            Imei::whereIn('id', $imeiIds)->update(['estado_imei' => Imei::ESTADO_EN_TRANSITO]);

        } else {
            $cantidad = (int) ($linea['cantidad'] ?? 0);

            if ($cantidad < 1) {
                throw new \Exception("Producto «{$producto->nombre}»: la cantidad debe ser al menos 1.");
            }

            $stockOrigen = StockAlmacen::where('producto_id', $producto->id)
                ->where('almacen_id', $almacenId)->first();

            if (!$stockOrigen || $stockOrigen->cantidad < $cantidad) {
                throw new \Exception(
                    "Producto «{$producto->nombre}»: stock insuficiente (disponible: " . ($stockOrigen->cantidad ?? 0) . ")."
                );
            }

            MovimientoInventario::create([
                'producto_id'        => $producto->id,
                'almacen_id'         => $almacenId,
                'almacen_destino_id' => $guia->almacen_destino_id,
                'user_id'            => $userId,
                'tipo_movimiento'    => $tipoMov,
                'cantidad'           => $cantidad,
                'variante_id'        => $linea['variante_id'] ?? null,
                'stock_anterior'     => $stockOrigen->cantidad,
                'stock_nuevo'        => $stockOrigen->cantidad - $cantidad,
                'numero_guia'        => $numeroGuia,
                'fecha_traslado'     => $guia->fecha_traslado->toDateString(),
                'observaciones'      => $linea['descripcion'] ?? null,
                'estado'             => 'pendiente',
            ]);

            $stockOrigen->decrement('cantidad', $cantidad);

            $total = StockAlmacen::where('producto_id', $producto->id)->sum('cantidad');
            $producto->update(['stock_actual' => $total]);
        }
    }

    private function resolverNumeroGuia(array $datos): string
    {
        if (!empty($datos['numero_guia'])) {
            return strtoupper(trim($datos['numero_guia']));
        }

        $ultimo = GuiaRemision::where('numero_guia', 'like', 'GR-%')
            ->latest('id')->value('numero_guia');

        $numero = $ultimo ? ((int) substr($ultimo, 3) + 1) : 1;

        return 'GR-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }

    private function calcularStockTotal(int $productoId, bool $esSerie, int $almacenId): int
    {
        if ($esSerie) {
            return Imei::where('producto_id', $productoId)
                ->where('estado_imei', Imei::ESTADO_EN_STOCK)->count();
        }
        return StockAlmacen::where('producto_id', $productoId)->sum('cantidad');
    }
}
