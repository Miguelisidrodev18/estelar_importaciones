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
use App\Models\AuditoriaVenta;
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

            $subtotal   = 0;
            $totalExact = 0;

            // Procesar cada detalle
            foreach ($detalles as $detalle) {
                $precioRecibido = (float) $detalle['precio_unitario'];
                $incluyeIgv     = (bool) ($detalle['incluye_igv'] ?? false);
                $qty            = (int)   $detalle['cantidad'];

                // precio_unitario en detalle_ventas siempre sin IGV (base imponible)
                $precioSinIgv   = $incluyeIgv ? round($precioRecibido / 1.18, 2) : $precioRecibido;
                // total con IGV por línea: usar precio original para no acumular error de redondeo
                $precioConIgv   = $incluyeIgv ? $precioRecibido : round($precioRecibido * 1.18, 2);

                $subtotalDetalle = round($precioSinIgv * $qty, 2);
                $subtotal       += $subtotalDetalle;
                $totalExact     += round($precioConIgv * $qty, 2);

                // Crear detalle de venta
                $detalleVenta = DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'producto_id'     => $detalle['producto_id'],
                    'variante_id'     => $detalle['variante_id'] ?? null,
                    'cantidad'        => $qty,
                    'precio_unitario' => $precioSinIgv,
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

            // Total exacto desde precios originales; IGV = total - base imponible
            $total = round($totalExact, 2);
            $igv   = round($total - $subtotal, 2);

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
                    $this->registrarEnCaja($venta, 'venta');
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
     * Editar campos no contables de una venta (tiempo limitado)
     */
    public function editarVenta(Venta $venta, array $datos, bool $requirioClave = false): Venta
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

        $datosAnteriores = $venta->only($camposPermitidos);

        $venta->update($actualizacion);

        $this->registrarAuditoria($venta, 'editar', $datosAnteriores, $venta->fresh()->only($camposPermitidos), $requirioClave);

        return $venta->fresh();
    }

    /**
     * Eliminar (soft-delete) una venta — revierte stock y oculta de las listas normales
     */
    public function eliminarVenta(Venta $venta, bool $requirioClave = false): void
    {
        if ($venta->estado_pago === 'anulado') {
            throw new \Exception('No se puede eliminar una venta ya anulada. Use la papelera directamente si lo necesita.');
        }

        // Si tiene cuenta por cobrar con pagos, no se puede eliminar
        if ($venta->es_credito && $venta->cuentaPorCobrar) {
            $cuenta = $venta->cuentaPorCobrar;
            if ($cuenta->pagos()->count() > 0) {
                throw new \Exception('No se puede eliminar esta venta: tiene pagos de crédito registrados. Anule los pagos primero.');
            }
        }

        DB::transaction(function () use ($venta, $requirioClave) {
            $datosAnteriores = $venta->only([
                'codigo', 'estado_pago', 'estado_sunat', 'total', 'cliente_id', 'fecha', 'tipo_comprobante',
            ]);

            $this->revertirStock($venta, 'Eliminación de venta #' . $venta->codigo);

            // Si es crédito sin pagos, anular la cuenta por cobrar y sus cuotas
            if ($venta->es_credito && $venta->cuentaPorCobrar) {
                $venta->cuentaPorCobrar->cuotas()->delete();
                $venta->cuentaPorCobrar->update(['estado' => 'anulado']);
            }

            // Si estaba pagada, registrar egreso en caja (devolución)
            if ($venta->estado_pago === 'pagado') {
                $this->registrarEnCaja($venta, 'eliminacion');
            }

            $this->registrarAuditoria($venta, 'eliminar', $datosAnteriores, null, $requirioClave);

            // SoftDelete — la venta desaparece de las listas normales
            $venta->delete();
        });
    }

    // ══════════════════════════════════════════════════════════════════
    // SUNAT — Motivos de Nota de Crédito (tabla 10 UBL 2.1)
    // ══════════════════════════════════════════════════════════════════
    public const MOTIVOS_NC = [
        '01' => 'Anulación de la operación',
        '02' => 'Anulación por error en el RUC',
        '03' => 'Corrección por error en la descripción',
        '04' => 'Descuento global',
        '05' => 'Descuento por ítem',
        '06' => 'Devolución total',
        '07' => 'Devolución por ítem',
        '08' => 'Bonificación',
        '09' => 'Disminución en el valor',
        '10' => 'Otros conceptos',
    ];

    /**
     * Generar Nota de Crédito para un comprobante ya aceptado por SUNAT.
     * Esta es la ÚNICA forma válida de cancelar facturas/boletas aceptadas.
     */
    public function generarNotaCredito(Venta $venta, string $motivoCodigo, bool $requirioClave = false): Venta
    {
        if (!array_key_exists($motivoCodigo, self::MOTIVOS_NC)) {
            throw new \Exception("Motivo de nota de crédito inválido: {$motivoCodigo}");
        }

        if ($venta->es_nota_credito) {
            throw new \Exception('No se puede generar una nota de crédito sobre otra nota de crédito.');
        }

        if ($venta->estado_pago === 'cotizacion') {
            throw new \Exception('Las cotizaciones no requieren nota de crédito.');
        }

        // Verificar que no tenga ya una NC de anulación activa
        $ncExistente = $venta->notasCredito()
            ->where('motivo_nc_codigo', '01')
            ->where('estado_pago', '!=', 'anulado')
            ->first();
        if ($ncExistente) {
            throw new \Exception("Ya existe una nota de crédito de anulación para este comprobante ({$ncExistente->codigo}).");
        }

        // Si tiene cuenta por cobrar con pagos, bloquear
        if ($venta->es_credito && $venta->cuentaPorCobrar && $venta->cuentaPorCobrar->pagos()->count() > 0) {
            throw new \Exception('No se puede generar NC: la venta tiene pagos de crédito registrados. Gestione la devolución manualmente.');
        }

        return DB::transaction(function () use ($venta, $motivoCodigo, $requirioClave) {
            $tipoNc      = $venta->tipo_comprobante === 'factura' ? 'nc_factura' : 'nc_boleta';
            $motivoDesc  = self::MOTIVOS_NC[$motivoCodigo];

            // Buscar serie de NC correspondiente al tipo de comprobante de origen
            $serieNc = \App\Models\SerieComprobante::where('sucursal_id', $venta->sucursal_id)
                ->where('tipo_comprobante', $tipoNc)
                ->where('activo', true)
                ->lockForUpdate()
                ->first();

            if (!$serieNc) {
                throw new \Exception(
                    "No existe una serie activa de {$tipoNc} para esta sucursal. " .
                    "Configure la serie en Administración → Series de Comprobantes."
                );
            }

            $correlativo = $serieNc->correlativo_actual + 1;
            $serieNc->update(['correlativo_actual' => $correlativo]);

            $datosAnteriores = $venta->only(['codigo', 'estado_pago', 'estado_sunat', 'total']);

            // Crear el registro de la Nota de Crédito
            $nc = Venta::create([
                'codigo'                 => 'NC-' . str_pad($correlativo, 5, '0', STR_PAD_LEFT),
                'user_id'                => auth()->id(),
                'cliente_id'             => $venta->cliente_id,
                'almacen_id'             => $venta->almacen_id,
                'sucursal_id'            => $venta->sucursal_id,
                'serie_comprobante_id'   => $serieNc->id,
                'correlativo'            => $correlativo,
                'fecha'                  => now()->toDateString(),
                'subtotal'               => $venta->subtotal,
                'igv'                    => $venta->igv,
                'total'                  => $venta->total,
                'tipo_comprobante'       => $tipoNc,
                'estado_pago'            => 'pagado',
                'estado_sunat'           => 'pendiente_envio',
                'condicion_pago'         => 'contado',
                'venta_origen_id'        => $venta->id,
                'motivo_nc_codigo'       => $motivoCodigo,
                'motivo_nc_descripcion'  => $motivoDesc,
                'observaciones'          => "NC {$motivoDesc} — Ref: {$venta->numero_documento}",
            ]);

            // Copiar los detalles del comprobante original
            $venta->load('detalles');
            foreach ($venta->detalles as $det) {
                \App\Models\DetalleVenta::create([
                    'venta_id'        => $nc->id,
                    'producto_id'     => $det->producto_id,
                    'variante_id'     => $det->variante_id,
                    'cantidad'        => $det->cantidad,
                    'precio_unitario' => $det->precio_unitario,
                    'subtotal'        => $det->subtotal,
                ]);
            }

            // Si la NC es por anulación (motivo 01 o 06 = devolución total) → revertir stock
            if (in_array($motivoCodigo, ['01', '02', '06'])) {
                $this->revertirStock($venta, 'Nota de Crédito #' . $nc->codigo);

                // Marcar comprobante original como anulado
                $venta->update([
                    'estado_pago'  => 'anulado',
                    'estado_sunat' => 'anulado_baja', // marcar que fue resuelto vía NC
                ]);

                // Si es crédito sin pagos, cerrar la cuenta
                if ($venta->es_credito && $venta->cuentaPorCobrar) {
                    $venta->cuentaPorCobrar->cuotas()->delete();
                    $venta->cuentaPorCobrar->update(['estado' => 'anulado']);
                }

                // Si estaba pagada → registrar egreso en caja
                if ($venta->estado_pago === 'pagado') {
                    $this->registrarEnCaja($venta, 'nota_credito');
                }
            }

            $this->registrarAuditoria($venta, 'anular', $datosAnteriores, ['nc_generada' => $nc->codigo], $requirioClave);

            Log::info('Nota de Crédito generada', [
                'nc_id'          => $nc->id,
                'nc_codigo'      => $nc->codigo,
                'venta_origen'   => $venta->codigo,
                'motivo_codigo'  => $motivoCodigo,
                'motivo_desc'    => $motivoDesc,
                'user_id'        => auth()->id(),
            ]);

            return $nc->fresh(['ventaOrigen', 'serieComprobante', 'detalles.producto']);
        });
    }

    /**
     * Anular una venta (revertir stock)
     * ⚠ Solo válido si el comprobante NO ha sido aceptado por SUNAT.
     * Si ya fue aceptado, usar generarNotaCredito().
     */
    public function anularVenta(Venta $venta, bool $requirioClave = false): void
    {
        if (in_array($venta->estado_pago, ['anulado', 'cotizacion'])) {
            throw new \Exception('Esta venta ya está anulada o es una cotización.');
        }

        // Bloquear si ya fue aceptada por SUNAT — debe usarse Nota de Crédito
        if ($venta->es_aceptado_sunat) {
            throw new \Exception(
                'Este comprobante fue aceptado por SUNAT y no puede anularse directamente. ' .
                'Debes generar una Nota de Crédito (motivo 01 - Anulación).'
            );
        }

        // Si tiene cuenta por cobrar con pagos, no se puede anular
        if ($venta->es_credito && $venta->cuentaPorCobrar) {
            $cuenta = $venta->cuentaPorCobrar;
            if ($cuenta->pagos()->count() > 0) {
                throw new \Exception('No se puede anular esta venta: tiene pagos de crédito registrados. Gestione la devolución manualmente.');
            }
        }

        DB::transaction(function () use ($venta, $requirioClave) {
            $datosAnteriores = $venta->only([
                'codigo', 'estado_pago', 'estado_sunat', 'total', 'cliente_id', 'fecha', 'tipo_comprobante',
            ]);

            $this->revertirStock($venta, 'Anulación de venta #' . $venta->codigo);

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

            $this->registrarAuditoria($venta, 'anular', $datosAnteriores, $venta->fresh()->only(['estado_pago']), $requirioClave);
        });
    }

    /**
     * Revertir stock de todos los detalles de una venta.
     * Reutilizado por anularVenta, eliminarVenta y generarNotaCredito.
     */
    private function revertirStock(Venta $venta, string $motivo): void
    {
        $venta->load('detalles');
        foreach ($venta->detalles as $detalle) {
            $stock = StockAlmacen::firstOrCreate(
                ['producto_id' => $detalle->producto_id, 'almacen_id' => $venta->almacen_id],
                ['cantidad' => 0]
            );

            $stockAnterior = $stock->cantidad;
            $stock->increment('cantidad', $detalle->cantidad);

            if ($detalle->imei_id) {
                Imei::where('id', $detalle->imei_id)->update([
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
                'motivo'          => $motivo,
                'estado'          => 'completado',
            ]);
        }
    }

    /**
     * Registrar una entrada en la bitácora de auditoría
     */
    private function registrarAuditoria(
        Venta $venta,
        string $accion,
        array $datosAnteriores,
        ?array $datosNuevos,
        bool $requirioClave
    ): void {
        AuditoriaVenta::create([
            'venta_id'         => $venta->id,
            'usuario_id'       => auth()->id(),
            'accion'           => $accion,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos'     => $datosNuevos,
            'requirio_clave'   => $requirioClave,
            'ip_address'       => request()->ip(),
        ]);
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
     * Registrar en caja.
     * $contexto: 'venta' (ingreso) | 'anulacion' | 'eliminacion' | 'nota_credito' (egreso/devolución)
     */
    private function registrarEnCaja(Venta $venta, string $contexto)
    {
        $caja = \App\Models\Caja::where('user_id', auth()->id())
            ->where('estado', 'abierta')
            ->first();

        if (!$caja) {
            return;
        }

        $esDevolucion = in_array($contexto, ['anulacion', 'eliminacion', 'nota_credito']);
        $tipo         = $esDevolucion ? 'egreso' : 'ingreso';

        $metodosValidos = ['efectivo', 'yape', 'plin', 'transferencia', 'mixto'];
        $metodoPago     = in_array($venta->metodo_pago, $metodosValidos)
            ? $venta->metodo_pago
            : 'efectivo';

        $conceptos = [
            'venta'       => 'Venta #' . $venta->codigo,
            'anulacion'   => 'Anulación venta #' . $venta->codigo,
            'eliminacion' => 'Eliminación venta #' . $venta->codigo,
            'nota_credito'=> 'Nota Crédito venta #' . $venta->codigo,
        ];
        $concepto = $conceptos[$contexto] ?? 'Venta #' . $venta->codigo;

        $cajaService = app(CajaService::class);

        // Para pagos mixtos: crear un movimiento por cada método con su referencia
        $pagosDetalle = $venta->pagos_detalle ?? [];
        if ($tipo === 'ingreso' && count($pagosDetalle) > 1) {
            foreach ($pagosDetalle as $pd) {
                $mp  = in_array($pd['metodo'] ?? '', $metodosValidos) ? $pd['metodo'] : 'efectivo';
                $ref = $pd['referencia'] ?? null;
                $cajaService->registrarMovimiento(
                    $caja->id, $tipo, (float) $pd['monto'],
                    $concepto, $venta->id, null, null, $mp, $ref
                );
            }
            return;
        }

        // Pago único: extraer referencia si existe
        $referencia = null;
        if ($tipo === 'ingreso' && count($pagosDetalle) === 1) {
            $referencia = $pagosDetalle[0]['referencia'] ?? null;
        }

        $cajaService->registrarMovimiento(
            $caja->id, $tipo, $venta->total,
            $concepto, $venta->id, null, null, $metodoPago, $referencia
        );
    }

    /**
     * Crear una cotización (no descuenta stock, no registra pago)
     */
    public function crearCotizacion(array $datosVenta, array $detalles)
    {
        return DB::transaction(function () use ($datosVenta, $detalles) {
            $datosVenta['tipo_comprobante'] = 'cotizacion';
            $datosVenta['estado_pago']      = 'cotizacion';

            $venta      = Venta::create($datosVenta);
            $subtotal   = 0;
            $totalExact = 0;

            foreach ($detalles as $detalle) {
                $precioRecibido  = (float) $detalle['precio_unitario'];
                $incluyeIgv      = (bool)  ($detalle['incluye_igv'] ?? false);
                $qty             = (int)   $detalle['cantidad'];

                $precioSinIgv    = $incluyeIgv ? round($precioRecibido / 1.18, 2) : $precioRecibido;
                $precioConIgv    = $incluyeIgv ? $precioRecibido : round($precioRecibido * 1.18, 2);
                $subtotalDetalle = round($precioSinIgv * $qty, 2);
                $subtotal       += $subtotalDetalle;
                $totalExact     += round($precioConIgv * $qty, 2);

                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'producto_id'     => $detalle['producto_id'],
                    'variante_id'     => $detalle['variante_id'] ?? null,
                    'cantidad'        => $qty,
                    'precio_unitario' => $precioSinIgv,
                    'subtotal'        => $subtotalDetalle,
                ]);
            }

            $total = round($totalExact, 2);
            $igv   = round($total - $subtotal, 2);

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

            $this->registrarEnCaja($venta, 'venta');

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

            $this->registrarEnCaja($venta, 'venta');
        });

        return $venta;
    }
}
