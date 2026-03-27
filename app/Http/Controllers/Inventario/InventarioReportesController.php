<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Catalogo\Marca;
use App\Models\DetalleCompra;
use App\Models\Imei;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Sucursal;
use Illuminate\Http\Request;

class InventarioReportesController extends Controller
{
    // ══════════════════════════════════════════════════════
    //  HU-INVENTARIO-06 — Stock Valorizado por Sucursal
    // ══════════════════════════════════════════════════════

    public function stockValorizado(Request $request)
    {
        $sucursales  = Sucursal::where('estado', 'activo')->orderBy('nombre')->get();
        $categorias  = Categoria::activas()->orderBy('nombre')->get();
        $marcas      = Marca::activos()->orderBy('nombre')->get();

        $sucursalId  = $request->sucursal_id;
        $categoriaId = $request->categoria_id;
        $marcaId     = $request->marca_id;

        $almacenId = null;
        if ($sucursalId) {
            $almacenId = Sucursal::find($sucursalId)?->almacen_id;
        }

        // ── Pre-calcular CPP por variante (Costo Promedio Ponderado real desde historial de compras)
        // Una sola query para todos los productos → evita N+1
        $cppPorVariante = DetalleCompra::whereNotNull('variante_id')
            ->selectRaw('variante_id, SUM(cantidad * precio_unitario) / NULLIF(SUM(cantidad), 0) AS cpp')
            ->groupBy('variante_id')
            ->pluck('cpp', 'variante_id')
            ->map(fn($v) => (float) $v);

        // CPP para productos SIN variante registrada en detalle_compras
        $cppPorProducto = DetalleCompra::whereNull('variante_id')
            ->selectRaw('producto_id, SUM(cantidad * precio_unitario) / NULLIF(SUM(cantidad), 0) AS cpp')
            ->groupBy('producto_id')
            ->pluck('cpp', 'producto_id')
            ->map(fn($v) => (float) $v);

        // ── Pre-cargar stock de IMEIs por variante (para productos tipo 'serie')
        $imeisPorVariante = Imei::where('estado_imei', 'en_stock')
            ->whereNotNull('variante_id')
            ->when($almacenId, fn($q) => $q->where('almacen_id', $almacenId))
            ->selectRaw('variante_id, COUNT(*) as total')
            ->groupBy('variante_id')
            ->pluck('total', 'variante_id')
            ->map(fn($v) => (int) $v);

        $productos = Producto::where('estado', 'activo')
            ->with([
                'categoria',
                'marca',
                'variantesActivas.color',
                'precios' => fn($q) => $q->where('activo', true)
                                         ->where('tipo_precio', 'venta_regular')
                                         ->orderByDesc('prioridad'),
            ])
            ->when($categoriaId, fn($q) => $q->where('categoria_id', $categoriaId))
            ->when($marcaId,     fn($q) => $q->where('marca_id', $marcaId))
            ->orderBy('nombre')
            ->get()
            ->map(function ($p) use ($almacenId, $cppPorVariante, $cppPorProducto, $imeisPorVariante) {
                $precioVentaBase = (float) ($p->precios->first()?->precio ?? 0);

                if ($p->variantesActivas->isNotEmpty()) {
                    // ── Producto CON variantes: desglosar por variante ──────────────
                    $variantesData = $p->variantesActivas->map(function ($v) use (
                        $almacenId, $cppPorVariante, $p, $precioVentaBase, $imeisPorVariante
                    ) {
                        // Stock por variante
                        if ($p->tipo_inventario === 'serie') {
                            $stock = (int) ($imeisPorVariante[$v->id] ?? 0);
                        } else {
                            // Para productos de cantidad: stock_actual de la variante
                            // (no hay desglose por almacén en stock_almacen para variantes)
                            $stock = $v->stock_actual;
                        }

                        // CPP: fuente primaria = historial de compras por variante
                        //      fallback 1 = costo almacenado en la variante
                        //      fallback 2 = costo del producto padre
                        $cpp = $cppPorVariante[$v->id]
                            ?? (float) ($v->costo_promedio ?: $v->ultimo_costo_compra ?: 0)
                            ?: (float) ($p->costo_promedio ?: $p->ultimo_costo_compra ?: 0);

                        $pv = $precioVentaBase + (float) $v->sobreprecio;

                        return [
                            'variante_id'  => $v->id,
                            'nombre'       => $v->nombre_completo,
                            'sku'          => $v->sku,
                            'color_hex'    => $v->color?->codigo_hex,
                            'stock'        => $stock,
                            'costo_cpp'    => $cpp,
                            'precio_venta' => $pv,
                            'valor_compra' => $stock * $cpp,
                            'valor_venta'  => $stock * $pv,
                            'utilidad'     => $stock * ($pv - $cpp),
                            'margen_pct'   => $pv > 0 ? round(($pv - $cpp) / $pv * 100, 1) : 0,
                        ];
                    })->filter(fn($v) => $v['stock'] > 0)->values();

                    $stockTotal = $variantesData->sum('stock');
                    $valCompra  = $variantesData->sum('valor_compra');
                    $valVenta   = $variantesData->sum('valor_venta');
                    $utilidad   = $variantesData->sum('utilidad');

                    return [
                        'id'             => $p->id,
                        'nombre'         => $p->nombre,
                        'categoria'      => $p->categoria?->nombre ?? '—',
                        'marca'          => $p->marca?->nombre     ?? '—',
                        'tipo'           => $p->tipo_inventario,
                        'tiene_variantes'=> true,
                        'variantes'      => $variantesData,
                        'stock'          => $stockTotal,
                        'precio_compra'  => $stockTotal > 0 ? round($valCompra / $stockTotal, 2) : 0,
                        'precio_venta'   => $stockTotal > 0 ? round($valVenta  / $stockTotal, 2) : 0,
                        'valor_compra'   => $valCompra,
                        'valor_venta'    => $valVenta,
                        'utilidad'       => $utilidad,
                        'margen_pct'     => $valVenta > 0 ? round($utilidad / $valVenta * 100, 1) : 0,
                    ];
                }

                // ── Producto SIN variantes ──────────────────────────────────────────
                $stock = $almacenId
                    ? $this->stockPorAlmacen($p, $almacenId)
                    : ($p->tipo_inventario === 'serie'
                        ? Imei::where('producto_id', $p->id)
                              ->where('estado_imei', 'en_stock')
                              ->when($almacenId, fn($q) => $q->where('almacen_id', $almacenId))
                              ->count()
                        : $p->stock_actual);

                $cpp = $cppPorProducto[$p->id]
                    ?? (float) ($p->costo_promedio ?: $p->ultimo_costo_compra ?: 0);
                $pv  = $precioVentaBase;

                return [
                    'id'             => $p->id,
                    'nombre'         => $p->nombre,
                    'categoria'      => $p->categoria?->nombre ?? '—',
                    'marca'          => $p->marca?->nombre     ?? '—',
                    'tipo'           => $p->tipo_inventario,
                    'tiene_variantes'=> false,
                    'variantes'      => collect(),
                    'stock'          => $stock,
                    'precio_compra'  => $cpp,
                    'precio_venta'   => $pv,
                    'valor_compra'   => $stock * $cpp,
                    'valor_venta'    => $stock * $pv,
                    'utilidad'       => $stock * ($pv - $cpp),
                    'margen_pct'     => $pv > 0 ? round(($pv - $cpp) / $pv * 100, 1) : 0,
                ];
            })
            ->filter(fn($p) => $p['stock'] > 0)
            ->values();

        $totales = [
            'items'        => $productos->count(),
            'unidades'     => $productos->sum('stock'),
            'valor_compra' => $productos->sum('valor_compra'),
            'valor_venta'  => $productos->sum('valor_venta'),
            'utilidad'     => $productos->sum('utilidad'),
        ];

        if ($request->export === 'csv') {
            return $this->exportarCsv($productos, $totales);
        }

        return view('inventario.reportes.stock-valorizado', compact(
            'productos', 'totales', 'sucursales', 'categorias', 'marcas',
            'sucursalId', 'categoriaId', 'marcaId'
        ));
    }

    private function stockPorAlmacen(Producto $p, int $almacenId): int
    {
        if ($p->tipo_inventario === 'serie') {
            return $p->imeis()
                ->where('almacen_id', $almacenId)
                ->where('estado_imei', 'en_stock')
                ->count();
        }

        $neto = MovimientoInventario::where('producto_id', $p->id)
            ->where('almacen_id', $almacenId)
            ->selectRaw("
                SUM(CASE WHEN tipo_movimiento IN ('ingreso','devolucion') THEN cantidad ELSE 0 END) -
                SUM(CASE WHEN tipo_movimiento IN ('salida','merma') THEN cantidad ELSE 0 END) AS neto
            ")
            ->value('neto');

        return max(0, (int) $neto);
    }

    private function exportarCsv($productos, array $totales)
    {
        $filename = 'stock-valorizado-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($productos, $totales) {
            $h = fopen('php://output', 'w');
            fprintf($h, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

            fputcsv($h, ['Producto','Variante','SKU','Categoría','Marca','Tipo','Stock','CPP (Costo Prom.)','Precio Venta','Val. Compra','Val. Venta','Margen %','Utilidad']);

            foreach ($productos as $p) {
                if ($p['tiene_variantes'] && $p['variantes']->isNotEmpty()) {
                    // Fila resumen del producto
                    fputcsv($h, [
                        $p['nombre'], 'TOTAL', '', $p['categoria'], $p['marca'], ucfirst($p['tipo']),
                        $p['stock'], '', '',
                        number_format($p['valor_compra'], 2),
                        number_format($p['valor_venta'],  2),
                        $p['margen_pct'].'%',
                        number_format($p['utilidad'], 2),
                    ]);
                    // Filas por variante
                    foreach ($p['variantes'] as $v) {
                        fputcsv($h, [
                            '  ↳ '.$p['nombre'],
                            $v['nombre'],
                            $v['sku'],
                            $p['categoria'], $p['marca'], ucfirst($p['tipo']),
                            $v['stock'],
                            number_format($v['costo_cpp'],    2),
                            number_format($v['precio_venta'], 2),
                            number_format($v['valor_compra'], 2),
                            number_format($v['valor_venta'],  2),
                            $v['margen_pct'].'%',
                            number_format($v['utilidad'], 2),
                        ]);
                    }
                } else {
                    fputcsv($h, [
                        $p['nombre'], '—', '—',
                        $p['categoria'], $p['marca'], ucfirst($p['tipo']),
                        $p['stock'],
                        number_format($p['precio_compra'], 2),
                        number_format($p['precio_venta'],  2),
                        number_format($p['valor_compra'],  2),
                        number_format($p['valor_venta'],   2),
                        $p['margen_pct'].'%',
                        number_format($p['utilidad'], 2),
                    ]);
                }
            }

            fputcsv($h, []);
            fputcsv($h, [
                'TOTALES','','','',$totales['unidades'],'','','',
                number_format($totales['valor_compra'], 2),
                number_format($totales['valor_venta'],  2),
                '',
                number_format($totales['utilidad'], 2),
            ]);

            fclose($h);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ══════════════════════════════════════════════════════
    //  HU-INVENTARIO-07 — Kardex Valorizado
    // ══════════════════════════════════════════════════════

    public function kardex(Request $request)
    {
        $productosList  = Producto::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']);
        $tiposMovimiento = ['ingreso','salida','ajuste','transferencia','devolucion','merma'];

        $productoId  = $request->producto_id;
        $desde       = $request->desde  ?? now()->startOfMonth()->format('Y-m-d');
        $hasta       = $request->hasta  ?? now()->format('Y-m-d');
        $tipoMov     = $request->tipo_movimiento;

        $movimientos = collect();
        $productoSel = null;
        $resumenKardex = null;

        if ($productoId) {
            $productoSel = Producto::with([
                'precios' => fn($q) => $q->where('activo', true)->where('tipo_precio', 'venta_regular')->orderByDesc('prioridad'),
            ])->find($productoId);

            $costoUnit = (float) ($productoSel?->costo_promedio ?: $productoSel?->ultimo_costo_compra ?: 0);
            $pvUnit    = (float) ($productoSel?->precios?->first()?->precio ?? 0);

            $rawMovs = MovimientoInventario::with(['almacen', 'usuario'])
                ->where('producto_id', $productoId)
                ->whereBetween('created_at', [$desde.' 00:00:00', $hasta.' 23:59:59'])
                ->when($tipoMov, fn($q) => $q->where('tipo_movimiento', $tipoMov))
                ->orderBy('created_at')
                ->get();

            $saldoQty = $saldoVal = 0;

            $movimientos = $rawMovs->map(function ($m) use (&$saldoQty, &$saldoVal, $costoUnit) {
                $esEntrada = in_array($m->tipo_movimiento, ['ingreso', 'devolucion']);
                $qty   = $m->cantidad;
                $valor = $qty * $costoUnit;

                if ($esEntrada) {
                    $saldoQty += $qty;
                    $saldoVal += $valor;
                    $inQty = $qty; $inVal = $valor;
                    $outQty = $outVal = 0;
                } else {
                    $saldoQty -= $qty;
                    $saldoVal -= $valor;
                    $outQty = $qty; $outVal = $valor;
                    $inQty = $inVal = 0;
                }

                return [
                    'fecha'       => $m->created_at->format('d/m/Y H:i'),
                    'tipo'        => $m->tipo_movimiento,
                    'almacen'     => $m->almacen?->nombre ?? '—',
                    'usuario'     => $m->usuario?->name   ?? '—',
                    'motivo'      => $m->motivo ?? $m->observaciones ?? '—',
                    'doc_ref'     => $m->documento_referencia ?? '—',
                    'costo_unit'  => $costoUnit,
                    'ingreso_qty' => $inQty,
                    'ingreso_val' => $inVal,
                    'salida_qty'  => $outQty,
                    'salida_val'  => $outVal,
                    'saldo_qty'   => max(0, $saldoQty),
                    'saldo_val'   => max(0, $saldoVal),
                ];
            });

            $resumenKardex = [
                'total_ingresos_qty' => $movimientos->sum('ingreso_qty'),
                'total_ingresos_val' => $movimientos->sum('ingreso_val'),
                'total_salidas_qty'  => $movimientos->sum('salida_qty'),
                'total_salidas_val'  => $movimientos->sum('salida_val'),
                'saldo_final_qty'    => $movimientos->last()['saldo_qty'] ?? 0,
                'saldo_final_val'    => $movimientos->last()['saldo_val'] ?? 0,
                'costo_unit'         => $costoUnit,
                'precio_venta'       => $pvUnit,
            ];
        }

        if ($request->export === 'csv' && $productoSel && $movimientos->isNotEmpty()) {
            return $this->exportarKardexCsv($productoSel, $movimientos, $resumenKardex, $desde, $hasta);
        }

        return view('inventario.reportes.kardex', compact(
            'productosList', 'movimientos', 'productoSel', 'resumenKardex',
            'productoId', 'desde', 'hasta', 'tipoMov', 'tiposMovimiento'
        ));
    }

    private function exportarKardexCsv($producto, $movimientos, array $resumen, string $desde, string $hasta)
    {
        $filename = 'kardex-'.str_slug($producto->nombre).'-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($producto, $movimientos, $resumen, $desde, $hasta) {
            $h = fopen('php://output', 'w');
            fprintf($h, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($h, ['Producto:', $producto->nombre]);
            fputcsv($h, ['Período:', $desde.' a '.$hasta]);
            fputcsv($h, ['Costo unitario:', number_format($resumen['costo_unit'], 2)]);
            fputcsv($h, []);
            fputcsv($h, ['Fecha','Tipo','Almacén','Doc. Ref.','Motivo','Costo U.','Ingreso Qty','Ingreso Val.','Salida Qty','Salida Val.','Saldo Qty','Saldo Val.']);

            foreach ($movimientos as $m) {
                fputcsv($h, [
                    $m['fecha'],
                    ucfirst($m['tipo']),
                    $m['almacen'],
                    $m['doc_ref'],
                    $m['motivo'],
                    number_format($m['costo_unit'], 2),
                    $m['ingreso_qty'] ?: '',
                    $m['ingreso_qty'] ? number_format($m['ingreso_val'], 2) : '',
                    $m['salida_qty'] ?: '',
                    $m['salida_qty'] ? number_format($m['salida_val'], 2) : '',
                    $m['saldo_qty'],
                    number_format($m['saldo_val'], 2),
                ]);
            }

            fputcsv($h, []);
            fputcsv($h, ['TOTALES','','','','','',$resumen['total_ingresos_qty'],number_format($resumen['total_ingresos_val'],2),$resumen['total_salidas_qty'],number_format($resumen['total_salidas_val'],2),$resumen['saldo_final_qty'],number_format($resumen['saldo_final_val'],2)]);
            fclose($h);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ══════════════════════════════════════════════════════
    //  HU-INVENTARIO-08 — Análisis ABC
    // ══════════════════════════════════════════════════════

    public function analisisAbc(Request $request)
    {
        $categorias  = Categoria::activas()->orderBy('nombre')->get();
        $marcas      = Marca::activos()->orderBy('nombre')->get();
        $categoriaId = $request->categoria_id;
        $marcaId     = $request->marca_id;

        $productos = Producto::where('estado', 'activo')
            ->with([
                'categoria', 'marca',
                'precios' => fn($q) => $q->where('activo', true)->where('tipo_precio', 'venta_regular')->orderByDesc('prioridad'),
            ])
            ->when($categoriaId, fn($q) => $q->where('categoria_id', $categoriaId))
            ->when($marcaId,     fn($q) => $q->where('marca_id', $marcaId))
            ->get()
            ->map(function ($p) {
                $stock = $p->tieneVariantes() ? (int) $p->stock_variantes : $p->stock_actual;
                $pc    = (float) ($p->costo_promedio ?: $p->ultimo_costo_compra ?: 0);
                $pv    = (float) ($p->precios->first()?->precio ?? 0);

                return [
                    'id'               => $p->id,
                    'nombre'           => $p->nombre,
                    'categoria'        => $p->categoria?->nombre ?? '—',
                    'marca'            => $p->marca?->nombre     ?? '—',
                    'stock'            => $stock,
                    'precio_compra'    => $pc,
                    'precio_venta'     => $pv,
                    'valor_inventario' => $stock * $pc,
                ];
            })
            ->filter(fn($p) => $p['valor_inventario'] > 0)
            ->sortByDesc('valor_inventario')
            ->values();

        $totalValor = $productos->sum('valor_inventario');
        $cumAcum = 0;

        $productos = $productos->map(function ($p, $i) use ($totalValor, &$cumAcum) {
            $pct     = $totalValor > 0 ? $p['valor_inventario'] / $totalValor * 100 : 0;
            $cumAcum += $pct;
            $clase   = $cumAcum <= 80 ? 'A' : ($cumAcum <= 95 ? 'B' : 'C');

            return array_merge($p, [
                'rank'           => $i + 1,
                'pct_valor'      => round($pct, 2),
                'pct_acum'       => round($cumAcum, 2),
                'clase'          => $clase,
                'recomendacion'  => match($clase) {
                    'A' => 'Control estricto — revisión continua — stock de seguridad alto',
                    'B' => 'Control moderado — revisión periódica mensual',
                    'C' => 'Control simplificado — pedidos menos frecuentes',
                },
            ]);
        });

        $total = $productos->count();
        $resumen = collect(['A','B','C'])->mapWithKeys(function ($clase) use ($productos, $total, $totalValor) {
            $grupo = $productos->where('clase', $clase);
            $val   = $grupo->sum('valor_inventario');
            return [$clase => [
                'count'         => $grupo->count(),
                'valor'         => $val,
                'pct_productos' => $total  > 0 ? round($grupo->count() / $total   * 100, 1) : 0,
                'pct_valor'     => $totalValor > 0 ? round($val / $totalValor * 100, 1) : 0,
            ]];
        });

        // Data for Chart.js Pareto
        $chartLabels    = $productos->take(30)->pluck('nombre')->map(fn($n) => mb_strimwidth($n, 0, 25, '…'))->values();
        $chartValores   = $productos->take(30)->pluck('pct_valor')->values();
        $chartAcumulado = $productos->take(30)->pluck('pct_acum')->values();

        if ($request->export === 'csv') {
            return $this->exportarAbcCsv($productos, $resumen, $totalValor);
        }

        return view('inventario.reportes.abc', compact(
            'productos', 'resumen', 'totalValor', 'categorias', 'marcas',
            'categoriaId', 'marcaId', 'chartLabels', 'chartValores', 'chartAcumulado'
        ));
    }

    private function exportarAbcCsv($productos, $resumen, float $totalValor)
    {
        $filename = 'analisis-abc-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($productos, $resumen, $totalValor) {
            $h = fopen('php://output', 'w');
            fprintf($h, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($h, ['#','Producto','Categoría','Marca','Stock','Costo U.','Valor Inv.','% Valor','% Acumulado','Clase','Recomendación']);

            foreach ($productos as $p) {
                fputcsv($h, [
                    $p['rank'],
                    $p['nombre'],
                    $p['categoria'],
                    $p['marca'],
                    $p['stock'],
                    number_format($p['precio_compra'], 2),
                    number_format($p['valor_inventario'], 2),
                    $p['pct_valor'].'%',
                    $p['pct_acum'].'%',
                    $p['clase'],
                    $p['recomendacion'],
                ]);
            }

            fputcsv($h, []);
            foreach (['A', 'B', 'C'] as $clase) {
                fputcsv($h, [
                    'Clase '.$clase,
                    $resumen[$clase]['count'].' productos ('.$resumen[$clase]['pct_productos'].'%)',
                    '',
                    '',
                    '',
                    '',
                    number_format($resumen[$clase]['valor'], 2),
                    $resumen[$clase]['pct_valor'].'%',
                ]);
            }
            fputcsv($h, ['TOTAL','','','','','',number_format($totalValor, 2),'100%']);

            fclose($h);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
