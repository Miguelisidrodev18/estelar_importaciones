<?php
// app/Services/VentaService.php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Imei;
use App\Models\StockAlmacen;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentaService
{
    protected $precioRotativoService;

    public function __construct(PrecioRotativoService $precioRotativoService)
    {
        $this->precioRotativoService = $precioRotativoService;
    }

    /**
     * Crear una nueva venta
     */
    public function crearVenta(array $datosVenta, array $detalles, ?array $pago = null)
    {
        return DB::transaction(function () use ($datosVenta, $detalles, $pago) {
            
            // Validar stock antes de procesar
            $this->validarStockDisponible($detalles, $datosVenta['almacen_id']);

            // Crear la venta
            $venta = Venta::create($datosVenta);

            $subtotal = 0;

            // Procesar cada detalle
            foreach ($detalles as $detalle) {
                // Usar el precio confirmado en el POS (el cajero ya lo validó)
                $precioUnitario  = (float) $detalle['precio_unitario'];
                $subtotalDetalle = $detalle['cantidad'] * $precioUnitario;
                $subtotal += $subtotalDetalle;

                // Crear detalle de venta
                $detalleVenta = DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'producto_id'     => $detalle['producto_id'],
                    'variante_id'     => $detalle['variante_id'] ?? null,
                    'cantidad'        => $detalle['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'subtotal'        => $subtotalDetalle,
                ]);

                // Si es producto con IMEI, marcar los IMEIs como vendidos
                if (!empty($detalle['imeis'])) {
                    $this->marcarImeisVendidos($detalle['imeis'], $venta->id, $detalleVenta->id);
                }

                // Descontar del stock
                $this->descontarStock(
                    $detalle['producto_id'],
                    $datosVenta['almacen_id'],
                    $detalle['cantidad'],
                    $detalle['imeis'] ?? []
                );
            }

            // Calcular IGV (18%)
            $igv = $subtotal * 0.18;
            $total = $subtotal + $igv;

            // Actualizar venta con montos calculados
            $venta->update([
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total
            ]);

            // Si hay pago, procesarlo
            if ($pago) {
                $this->procesarPago($venta, $pago);
            }

            // Registrar movimiento en caja si se procesó un pago
            if ($pago) {
                $this->registrarEnCaja($venta, $pago['metodo_pago'] ?? 'efectivo');
            }

            Log::info('Venta creada', [
                'venta_id' => $venta->id,
                'user_id' => $venta->user_id,
                'total' => $venta->total
            ]);

            return $venta->fresh(['detalles.producto', 'cliente']);
        });
    }

    /**
     * Validar stock disponible antes de la venta
     */
    private function validarStockDisponible(array $detalles, int $almacenId)
    {
        foreach ($detalles as $detalle) {
            // Productos serie/IMEI: el stock se controla por unidad individual
            if (!empty($detalle['imeis'])) {
                $this->validarImeisDisponibles($detalle['imeis'], $almacenId);
                continue;
            }

            $stock = StockAlmacen::where('producto_id', $detalle['producto_id'])
                ->where('almacen_id', $almacenId)
                ->first();

            if (!$stock || $stock->cantidad < $detalle['cantidad']) {
                $producto = \App\Models\Producto::find($detalle['producto_id']);
                throw new \Exception("Stock insuficiente para {$producto->nombre}. Disponible: " . ($stock->cantidad ?? 0));
            }
        }
    }

    /**
     * Validar que los IMEIs estén disponibles
     */
    private function validarImeisDisponibles(array $imeis, int $almacenId)
    {
        $codigosImei = array_column($imeis, 'codigo_imei');
        
        $existentes = Imei::whereIn('codigo_imei', $codigosImei)
            ->where('almacen_id', $almacenId)
            ->where('estado_imei', 'en_stock')
            ->get();

        if ($existentes->count() !== count($codigosImei)) {
            $encontrados = $existentes->pluck('codigo_imei')->toArray();
            $faltantes = array_diff($codigosImei, $encontrados);
            throw new \Exception("Los siguientes IMEIs no están disponibles: " . implode(', ', $faltantes));
        }
    }

    /**
     * Marcar IMEIs como vendidos
     */
    private function marcarImeisVendidos(array $imeis, int $ventaId, int $detalleVentaId)
    {
        foreach ($imeis as $imeiData) {
            Imei::where('codigo_imei', $imeiData['codigo_imei'])
                ->update([
                    'estado_imei' => 'vendido',
                    'fecha_venta'  => now(),
                    'venta_id'     => $ventaId,
                ]);
        }
    }

    /**
     * Descontar stock del almacén
     */
    private function descontarStock(int $productoId, int $almacenId, int $cantidad, array $imeis = [])
    {
        $producto      = \App\Models\Producto::find($productoId);
        $stockAnterior = 0;
        $stockNuevo    = 0;

        if (!empty($imeis)) {
            // Producto serie/IMEI: el stock se controla por IMEI individual.
            // Los IMEIs ya fueron marcados como 'vendido' antes de llegar aquí.
            $stockNuevo    = Imei::where('producto_id', $productoId)
                                 ->where('almacen_id', $almacenId)
                                 ->where('estado_imei', 'en_stock')
                                 ->count();
            $stockAnterior = $stockNuevo + $cantidad;

            // Actualizar stock_actual global del producto
            $totalStock = Imei::where('producto_id', $productoId)
                               ->where('estado_imei', 'en_stock')
                               ->count();
            $producto->update(['stock_actual' => $totalStock]);
        } else {
            // Producto por cantidad: usar StockAlmacen
            $stock = StockAlmacen::where('producto_id', $productoId)
                ->where('almacen_id', $almacenId)
                ->first();

            if ($stock) {
                $stockAnterior = $stock->cantidad;
                $stock->decrement('cantidad', $cantidad);
                $stockNuevo = $stock->cantidad;

                $totalStock = StockAlmacen::where('producto_id', $productoId)->sum('cantidad');
                $producto->update(['stock_actual' => $totalStock]);
            }
        }

        // Registrar movimiento de inventario
        MovimientoInventario::create([
            'producto_id'     => $productoId,
            'almacen_id'      => $almacenId,
            'user_id'         => auth()->id(),
            'tipo_movimiento' => 'salida',
            'cantidad'        => $cantidad,
            'stock_anterior'  => $stockAnterior,
            'stock_nuevo'     => $stockNuevo,
            'motivo'          => 'Venta',
            'estado'          => 'completado',
            'imeis'           => !empty($imeis) ? json_encode($imeis) : null,
        ]);
    }

    /**
     * Procesar pago de la venta
     */
    private function procesarPago(Venta $venta, array $pago)
    {
        // Aquí iría la lógica de pago (conexión con pasarela, etc.)
        $venta->update([
            'estado_pago' => 'pagado',
            'metodo_pago' => $pago['metodo_pago'],
            'fecha_confirmacion' => now(),
            'usuario_confirma_id' => auth()->id()
        ]);
    }

    /**
     * Registrar en caja
     */
    private function registrarEnCaja(Venta $venta, string $metodoPago)
    {
        // Buscar caja abierta del usuario
        $caja = \App\Models\Caja::where('user_id', auth()->id())
            ->where('estado', 'abierta')
            ->first();

        if ($caja) {
            $cajaService = app(CajaService::class);
            $cajaService->registrarMovimiento(
                $caja->id,
                'ingreso',
                $venta->total,
                'Venta #' . $venta->codigo,
                $venta->id,  // venta_id
                null,         // compra_id
                null,         // observaciones
                $metodoPago,  // metodo_pago
                null          // referencia
            );
        }
    }

    /**
     * Confirmar pago de una venta pendiente
     */
    public function confirmarPago(int $ventaId, string $metodoPago, int $usuarioId)
    {
        $venta = Venta::findOrFail($ventaId);

        if ($venta->estado_pago !== 'pendiente') {
            throw new \Exception('La venta ya ha sido procesada');
        }

        DB::transaction(function () use ($venta, $metodoPago, $usuarioId) {
            $venta->update([
                'estado_pago' => 'pagado',
                'metodo_pago' => $metodoPago,
                'usuario_confirma_id' => $usuarioId,
                'fecha_confirmacion' => now()
            ]);

            $this->registrarEnCaja($venta, $metodoPago);
        });

        return $venta;
    }

    /**
     * Anular una venta (revertir stock)
     */
    public function anularVenta(Venta $venta)
    {
        if ($venta->estado_pago !== 'pagado') {
            throw new \Exception('Solo se pueden anular ventas pagadas');
        }

        DB::transaction(function () use ($venta) {
            // Revertir stock de cada detalle
            foreach ($venta->detalles as $detalle) {
                $stock = StockAlmacen::firstOrCreate(
                    [
                        'producto_id' => $detalle->producto_id,
                        'almacen_id' => $venta->almacen_id
                    ],
                    ['cantidad' => 0]
                );

                $stockAnterior = $stock->cantidad;
                $stock->increment('cantidad', $detalle->cantidad);

                // Si tenía IMEIs, devolverlos
                if ($detalle->imei_id) {
                    Imei::where('id', $detalle->imei_id)
                        ->update([
                            'estado_imei' => 'en_stock',
                            'venta_id' => null,
                            'detalle_venta_id' => null,
                            'fecha_venta' => null
                        ]);
                }

                // Registrar movimiento de reversión
                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_id,
                    'almacen_id' => $venta->almacen_id,
                    'user_id' => auth()->id(),
                    'tipo_movimiento' => 'ingreso',
                    'cantidad' => $detalle->cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stock->cantidad,
                    'motivo' => 'Anulación de venta #' . $venta->id,
                    'estado' => 'completado'
                ]);
            }

            $venta->update([
                'estado_pago' => 'anulado'
            ]);

            // Registrar en caja (egreso)
            $this->registrarEnCaja($venta, 'anulacion');
        });
    }
}