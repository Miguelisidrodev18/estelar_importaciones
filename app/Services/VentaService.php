<?php
// app/Services/VentaService.php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\GuiaRemision;
use App\Models\Imei;
use App\Models\StockAlmacen;
use App\Models\MovimientoInventario;
use App\Models\CuentaPorCobrar;
use App\Models\CuotaCobro;
use App\Models\PagoCredito;
use Carbon\Carbon;
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
    public function crearVenta(array $datosVenta, array $detalles, ?array $pago = null, ?array $creditoData = null)
    {
        return DB::transaction(function () use ($datosVenta, $detalles, $pago, $creditoData) {

            // Extraer guia_data antes de crear la venta (no es columna de ventas)
            $guiaData = $datosVenta['guia_data'] ?? null;
            unset($datosVenta['guia_data']);

            $esCredito = ($datosVenta['condicion_pago'] ?? 'contado') === 'credito' || !empty($creditoData);

            // Validar stock antes de procesar
            $this->validarStockDisponible($detalles, $datosVenta['almacen_id']);

            // Para ventas a crédito, el estado inicial es 'credito' (sin pago inmediato)
            if ($esCredito) {
                $datosVenta['estado_pago']   = 'credito';
                $datosVenta['es_credito']    = true;
                $datosVenta['condicion_pago']= 'credito';
                $datosVenta['metodo_pago']   = null;
            }

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
                    $detalle['imeis'] ?? [],
                    $detalle['variante_id'] ?? null
                );
            }

            // Calcular IGV (18%)
            $igv   = $subtotal * 0.18;
            $total = $subtotal + $igv;

            // Actualizar venta con montos calculados
            $venta->update([
                'subtotal' => $subtotal,
                'igv'      => $igv,
                'total'    => $total,
            ]);

            // Crear guía de remisión si se proporcionaron datos
            if ($guiaData) {
                GuiaRemision::create(array_merge(['venta_id' => $venta->id], $guiaData));
            }

            if ($esCredito) {
                // Crear cuenta por cobrar con las cuotas
                $this->crearCuentaPorCobrar($venta, $creditoData ?? []);
            } else {
                // Si hay pago, procesarlo y registrar en caja
                if ($pago) {
                    $this->procesarPago($venta, $pago);
                    $this->registrarEnCaja($venta, $pago['metodo_pago'] ?? 'efectivo');
                }
            }

            Log::info('Venta creada', [
                'venta_id'   => $venta->id,
                'user_id'    => $venta->user_id,
                'total'      => $venta->total,
                'es_credito' => $esCredito,
            ]);

            return $venta->fresh(['detalles.producto', 'cliente']);
        });
    }

    /**
     * Crear la cuenta por cobrar y sus cuotas para una venta a crédito
     */
    private function crearCuentaPorCobrar(Venta $venta, array $creditoData): CuentaPorCobrar
    {
        $numeroCuotas    = (int) ($creditoData['numero_cuotas'] ?? 1);
        $diasEntreCuotas = (int) ($creditoData['dias_entre_cuotas'] ?? 30);
        $fechaInicio     = Carbon::parse($creditoData['fecha_inicio'] ?? now()->toDateString());

        $fechaVencimientoFinal = $fechaInicio->copy()->addDays($diasEntreCuotas * $numeroCuotas);

        $cuenta = CuentaPorCobrar::create([
            'venta_id'                => $venta->id,
            'cliente_id'              => $venta->cliente_id,
            'user_id'                 => auth()->id(),
            'monto_total'             => $venta->total,
            'monto_pagado'            => 0,
            'numero_cuotas'           => $numeroCuotas,
            'dias_entre_cuotas'       => $diasEntreCuotas,
            'fecha_inicio'            => $fechaInicio,
            'fecha_vencimiento_final' => $fechaVencimientoFinal,
            'estado'                  => 'vigente',
        ]);

        // Calcular monto por cuota (la primera absorbe el redondeo)
        $montoPorCuota  = round($venta->total / $numeroCuotas, 2);
        $montoTotal     = $montoPorCuota * $numeroCuotas;
        $diferencia     = round($venta->total - $montoTotal, 2);

        for ($i = 1; $i <= $numeroCuotas; $i++) {
            $monto = $montoPorCuota;
            if ($i === 1) {
                $monto = round($monto + $diferencia, 2);
            }

            CuotaCobro::create([
                'cuenta_por_cobrar_id' => $cuenta->id,
                'numero_cuota'         => $i,
                'total_cuotas'         => $numeroCuotas,
                'monto'                => $monto,
                'fecha_vencimiento'    => $fechaInicio->copy()->addDays($diasEntreCuotas * $i),
                'estado'               => 'pendiente',
            ]);
        }

        return $cuenta;
    }

    /**
     * Registrar un pago de crédito contra una cuenta por cobrar
     */
    public function registrarPagoCredito(CuentaPorCobrar $cuenta, array $pagoData): PagoCredito
    {
        return DB::transaction(function () use ($cuenta, $pagoData) {
            $monto = (float) $pagoData['monto'];

            // Crear registro de pago
            $pago = PagoCredito::create([
                'cuenta_por_cobrar_id' => $cuenta->id,
                'cuota_cobro_id'       => $pagoData['cuota_cobro_id'] ?? null,
                'usuario_id'           => auth()->id(),
                'monto'                => $monto,
                'fecha_pago'           => $pagoData['fecha_pago'] ?? now()->toDateString(),
                'metodo_pago'          => $pagoData['metodo_pago'],
                'referencia'           => $pagoData['referencia'] ?? null,
                'observaciones'        => $pagoData['observaciones'] ?? null,
            ]);

            // Actualizar monto pagado y fecha último pago en la cuenta
            $nuevoPagado = round((float) $cuenta->monto_pagado + $monto, 2);
            $cuenta->update([
                'monto_pagado'    => $nuevoPagado,
                'fecha_ultimo_pago'=> now()->toDateString(),
            ]);

            // Marcar la cuota asociada como pagada (si se especificó)
            if (!empty($pagoData['cuota_cobro_id'])) {
                $cuota = CuotaCobro::find($pagoData['cuota_cobro_id']);
                if ($cuota && $cuota->estado === 'pendiente') {
                    $cuota->update([
                        'estado'         => 'pagado',
                        'fecha_pago_real'=> now()->toDateString(),
                    ]);
                }
            }

            // Si el monto pagado cubre el total, cerrar la cuenta y actualizar la venta
            if ($nuevoPagado >= (float) $cuenta->monto_total) {
                $cuenta->update(['estado' => 'pagado']);
                $cuenta->venta->update([
                    'estado_pago'         => 'pagado',
                    'fecha_confirmacion'  => now(),
                    'usuario_confirma_id' => auth()->id(),
                ]);
                // Registrar en caja el ingreso total (monto de este pago)
                $this->registrarEnCajaCredito($cuenta->venta, $monto, $pagoData['metodo_pago']);
            } else {
                // Registrar el pago parcial en caja
                $this->registrarEnCajaCredito($cuenta->venta, $monto, $pagoData['metodo_pago']);
            }

            Log::info('Pago de crédito registrado', [
                'cuenta_id' => $cuenta->id,
                'monto'     => $monto,
                'pagado'    => $nuevoPagado,
                'total'     => $cuenta->monto_total,
            ]);

            return $pago;
        });
    }

    /**
     * Editar campos no contables de una venta (tiempo limitado, solo Admin)
     */
    public function editarVenta(Venta $venta, array $datos): Venta
    {
        if ($venta->estado_pago === 'anulado') {
            throw new \Exception('No se puede editar una venta anulada.');
        }

        // Si es crédito con pagos registrados, no permitir edición
        if ($venta->es_credito && $venta->cuentaPorCobrar && $venta->cuentaPorCobrar->pagos()->count() > 0) {
            throw new \Exception('No se puede editar una venta a crédito que ya tiene pagos registrados.');
        }

        $horasTranscurridas = $venta->created_at->diffInHours(now());
        $ventanaMaxima      = config('ventas.edit_window_hours', 24);

        if ($horasTranscurridas > $ventanaMaxima) {
            throw new \Exception("Solo se pueden editar comprobantes dentro de las {$ventanaMaxima} horas de su emisión.");
        }

        $camposPermitidos = ['observaciones', 'metodo_pago', 'fecha'];
        $actualizacion    = array_intersect_key($datos, array_flip($camposPermitidos));

        Log::info('Venta editada', [
            'venta_id'   => $venta->id,
            'user_id'    => auth()->id(),
            'cambios'    => $actualizacion,
            'horas_desde_creacion' => $horasTranscurridas,
        ]);

        $venta->update($actualizacion);

        return $venta->fresh();
    }

    /**
     * Anular una venta (revertir stock)
     */
    public function anularVenta(Venta $venta): void
    {
        if (in_array($venta->estado_pago, ['anulado', 'cotizacion'])) {
            throw new \Exception('Esta venta ya está anulada o es una cotización.');
        }

        // Si tiene cuenta por cobrar con pagos, no se puede anular
        if ($venta->es_credito && $venta->cuentaPorCobrar) {
            $cuenta = $venta->cuentaPorCobrar;
            if ($cuenta->pagos()->count() > 0) {
                throw new \Exception('No se puede anular esta venta: tiene pagos de crédito registrados. Gestione la devolución manualmente.');
            }
        }

        DB::transaction(function () use ($venta) {
            // Revertir stock de cada detalle
            $venta->load('detalles');
            foreach ($venta->detalles as $detalle) {
                $stock = StockAlmacen::firstOrCreate(
                    [
                        'producto_id' => $detalle->producto_id,
                        'almacen_id'  => $venta->almacen_id,
                    ],
                    ['cantidad' => 0]
                );

                $stockAnterior = $stock->cantidad;
                $stock->increment('cantidad', $detalle->cantidad);

                // Si tenía IMEIs, devolverlos
                if ($detalle->imei_id) {
                    Imei::where('id', $detalle->imei_id)
                        ->update([
                            'estado_imei'      => 'en_stock',
                            'venta_id'         => null,
                            'detalle_venta_id' => null,
                            'fecha_venta'      => null,
                        ]);
                }

                MovimientoInventario::create([
                    'producto_id'     => $detalle->producto_id,
                    'almacen_id'      => $venta->almacen_id,
                    'user_id'         => auth()->id(),
                    'tipo_movimiento' => 'ingreso',
                    'cantidad'        => $detalle->cantidad,
                    'stock_anterior'  => $stockAnterior,
                    'stock_nuevo'     => $stock->cantidad,
                    'motivo'          => 'Anulación de venta #' . $venta->codigo,
                    'estado'          => 'completado',
                ]);
            }

            // Si es crédito sin pagos, anular la cuenta por cobrar y sus cuotas
            if ($venta->es_credito && $venta->cuentaPorCobrar) {
                $venta->cuentaPorCobrar->cuotas()->delete();
                $venta->cuentaPorCobrar->update(['estado' => 'anulado']);
            }

            // Si estaba pagada, registrar egreso en caja (devolución)
            if ($venta->estado_pago === 'pagado') {
                $this->registrarEnCaja($venta, 'anulacion');
            }

            $venta->update(['estado_pago' => 'anulado']);
        });
    }

    /**
     * Registrar ingreso en caja por pago parcial de crédito
     */
    private function registrarEnCajaCredito(Venta $venta, float $monto, string $metodoPago): void
    {
        $caja = \App\Models\Caja::where('user_id', auth()->id())
            ->where('estado', 'abierta')
            ->first();

        if ($caja) {
            $cajaService = app(CajaService::class);
            $cajaService->registrarMovimiento(
                $caja->id,
                'ingreso',
                $monto,
                'Pago crédito venta #' . $venta->codigo,
                $venta->id,
                null,
                null,
                $metodoPago,
                null
            );
        }
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
            $faltantes   = array_diff($codigosImei, $encontrados);
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
    private function descontarStock(int $productoId, int $almacenId, int $cantidad, array $imeis = [], ?int $varianteId = null)
    {
        $producto      = \App\Models\Producto::find($productoId);
        $stockAnterior = 0;
        $stockNuevo    = 0;

        if (!empty($imeis)) {
            $stockNuevo    = Imei::where('producto_id', $productoId)
                                 ->where('almacen_id', $almacenId)
                                 ->where('estado_imei', 'en_stock')
                                 ->count();
            $stockAnterior = $stockNuevo + $cantidad;

            $totalStock = Imei::where('producto_id', $productoId)
                               ->where('estado_imei', 'en_stock')
                               ->count();
            $producto->update(['stock_actual' => $totalStock]);

            if ($varianteId) {
                $stockVariante = Imei::where('variante_id', $varianteId)
                                     ->where('estado_imei', 'en_stock')
                                     ->count();
                \App\Models\ProductoVariante::where('id', $varianteId)
                    ->update(['stock_actual' => $stockVariante]);
            }
        } else {
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

            if ($varianteId) {
                $variante = \App\Models\ProductoVariante::find($varianteId);
                if ($variante) {
                    $nuevoStock = max(0, $variante->stock_actual - $cantidad);
                    $variante->update(['stock_actual' => $nuevoStock]);
                }
            }
        }

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
        $venta->update([
            'estado_pago'         => 'pagado',
            'metodo_pago'         => $pago['metodo_pago'],
            'fecha_confirmacion'  => now(),
            'usuario_confirma_id' => auth()->id(),
        ]);
    }

    /**
     * Registrar en caja
     */
    private function registrarEnCaja(Venta $venta, string $metodoPago)
    {
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
                $venta->id,
                null,
                null,
                $metodoPago,
                null
            );
        }
    }

    /**
     * Crear una cotización (no descuenta stock, no registra pago)
     */
    public function crearCotizacion(array $datosVenta, array $detalles)
    {
        return DB::transaction(function () use ($datosVenta, $detalles) {
            $datosVenta['tipo_comprobante'] = 'cotizacion';
            $datosVenta['estado_pago']      = 'cotizacion';

            $venta    = Venta::create($datosVenta);
            $subtotal = 0;

            foreach ($detalles as $detalle) {
                $precioUnitario  = (float) $detalle['precio_unitario'];
                $subtotalDetalle = $detalle['cantidad'] * $precioUnitario;
                $subtotal       += $subtotalDetalle;

                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'producto_id'     => $detalle['producto_id'],
                    'variante_id'     => $detalle['variante_id'] ?? null,
                    'cantidad'        => $detalle['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'subtotal'        => $subtotalDetalle,
                ]);
            }

            $igv   = $subtotal * 0.18;
            $total = $subtotal + $igv;

            $venta->update(['subtotal' => $subtotal, 'igv' => $igv, 'total' => $total]);

            Log::info('Cotización creada', ['venta_id' => $venta->id, 'user_id' => $venta->user_id]);

            return $venta->fresh(['detalles.producto', 'cliente']);
        });
    }

    /**
     * Convertir una cotización a boleta o factura (descuenta stock y registra pago)
     */
    public function convertirAVenta(Venta $venta, string $tipoComprobante, string $metodoPago)
    {
        if ($venta->tipo_comprobante !== 'cotizacion') {
            throw new \Exception('Solo se pueden convertir cotizaciones');
        }

        return DB::transaction(function () use ($venta, $tipoComprobante, $metodoPago) {
            $venta->load('detalles');

            $detalles = $venta->detalles->map(fn($d) => [
                'producto_id'     => $d->producto_id,
                'variante_id'     => $d->variante_id,
                'cantidad'        => $d->cantidad,
                'precio_unitario' => (float) $d->precio_unitario,
                'imeis'           => [],
            ])->toArray();

            $this->validarStockDisponible($detalles, $venta->almacen_id);

            foreach ($detalles as $detalle) {
                $this->descontarStock(
                    $detalle['producto_id'],
                    $venta->almacen_id,
                    $detalle['cantidad'],
                    [],
                    $detalle['variante_id'] ?? null
                );
            }

            $venta->update([
                'tipo_comprobante'    => $tipoComprobante,
                'estado_pago'         => 'pagado',
                'metodo_pago'         => $metodoPago,
                'fecha_confirmacion'  => now(),
                'usuario_confirma_id' => auth()->id(),
            ]);

            $this->registrarEnCaja($venta, $metodoPago);

            Log::info('Cotización convertida a venta', [
                'venta_id'         => $venta->id,
                'tipo_comprobante' => $tipoComprobante,
            ]);

            return $venta->fresh();
        });
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
                'estado_pago'         => 'pagado',
                'metodo_pago'         => $metodoPago,
                'usuario_confirma_id' => $usuarioId,
                'fecha_confirmacion'  => now(),
            ]);

            $this->registrarEnCaja($venta, $metodoPago);
        });

        return $venta;
    }
}
