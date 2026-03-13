<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\Imei;
use App\Models\StockAlmacen;
use App\Models\Sucursal;
use App\Services\VentaService;
use App\Services\VarianteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $ventas = Venta::with('vendedor', 'cliente', 'almacen')
            ->when($user->role->nombre === 'Vendedor', fn($q) => $q->where('user_id', $user->id))
            ->when($user->almacen_id, fn($q) => $q->where('almacen_id', $user->almacen_id))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $statsBase = Venta::query()
            ->when($user->role->nombre === 'Vendedor', fn($q) => $q->where('user_id', $user->id))
            ->when($user->almacen_id, fn($q) => $q->where('almacen_id', $user->almacen_id));

        $stats = [
            'hoy'        => (clone $statsBase)->whereDate('fecha', today())->sum('total'),
            'mes_total'  => (clone $statsBase)->whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)->sum('total'),
            'mes_count'  => (clone $statsBase)->whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)->count(),
            'pendientes' => (clone $statsBase)->where('estado_pago', 'pendiente')->count(),
        ];

        return view('ventas.index', compact('ventas', 'stats'));
    }

    public function create()
    {
        $user      = auth()->user();
        $clientes  = Cliente::activos()->orderBy('nombre')->get();
        $categorias = Categoria::activas()->orderBy('nombre')->get();

        // Filtrar almacenes por rol: admin ve todos, el resto solo el suyo
        if ($user->role->nombre === 'Administrador') {
            $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();
        } else {
            $almacenes = Almacen::where('estado', 'activo')
                ->where('id', $user->almacen_id)
                ->orderBy('nombre')
                ->get();
        }

        // Preseleccionar si solo hay un almacén disponible
        $almacenPredeterminado = $almacenes->count() === 1
            ? $almacenes->first()->id
            : ($user->almacen_id ?: null);

        $productos = Producto::where('estado', 'activo')
            ->with(['categoria', 'variantesActivas.color', 'precios' => function ($q) {
                $q->where('activo', true)
                  ->whereNull('almacen_id')
                  ->where('tipo_precio', 'venta_regular')
                  ->latest();
            }])
            ->orderBy('nombre')
            ->get();

        // Stock por almacén para productos de cantidad
        $stockPorAlmacen = StockAlmacen::whereIn('producto_id', $productos->pluck('id'))
            ->get()
            ->groupBy('producto_id')
            ->map(fn($rows) => $rows->pluck('cantidad', 'almacen_id'));

        // Stock por almacén para productos de serie (contar IMEIs en_stock) — nivel producto
        $imeisPorAlmacen = Imei::whereIn('producto_id', $productos->pluck('id'))
            ->where('estado_imei', 'en_stock')
            ->selectRaw('producto_id, almacen_id, COUNT(*) as total')
            ->groupBy('producto_id', 'almacen_id')
            ->get()
            ->groupBy('producto_id')
            ->map(fn($rows) => $rows->pluck('total', 'almacen_id'));

        // Stock por almacén POR VARIANTE (solo serie tienen IMEI con variante_id)
        $imeisPorVariante = Imei::whereIn('producto_id', $productos->pluck('id'))
            ->where('estado_imei', 'en_stock')
            ->whereNotNull('variante_id')
            ->selectRaw('variante_id, almacen_id, COUNT(*) as total')
            ->groupBy('variante_id', 'almacen_id')
            ->get()
            ->groupBy('variante_id')
            ->map(fn($rows) => $rows->pluck('total', 'almacen_id'));

        $productos = $productos->map(function($p) use ($stockPorAlmacen, $imeisPorAlmacen, $imeisPorVariante) {
            $precioActivo = $p->precios->first();
            $esSerie      = $p->tipo_inventario === 'serie';

            $variantes = $p->variantesActivas->map(fn($v) => [
                'id'                => $v->id,
                'sku'               => $v->sku,
                'color_id'          => $v->color_id,
                'color_nombre'      => $v->color?->nombre,
                'color_hex'         => $v->color?->codigo_hex,
                'capacidad'         => $v->capacidad,
                'sobreprecio'       => (float)$v->sobreprecio,
                'stock_actual'      => (int)$v->stock_actual,
                'stock_por_almacen' => $esSerie
                    ? ($imeisPorVariante[$v->id] ?? collect())->toArray()
                    : [],   // para no-serie no hay desglose por almacén
                'nombre_completo'   => $v->nombre_completo,
                'tiene_stock'       => $v->tieneStock(),
            ]);

            $stockMap = $p->tipo_inventario === 'serie'
                ? ($imeisPorAlmacen[$p->id] ?? collect())->toArray()
                : ($stockPorAlmacen[$p->id] ?? collect())->toArray();

            return [
                'id'               => $p->id,
                'nombre'           => $p->nombre,
                'codigo'           => $p->codigo,
                'codigo_barras'    => $p->codigo_barras ?? null,
                'categoria_id'     => $p->categoria_id,
                'tipo_inventario'  => $p->tipo_inventario,
                'stock_actual'     => (int) $p->stock_actual,
                'stock_por_almacen'=> $stockMap,  // {almacen_id: qty}
                'precio_venta'     => (float) ($precioActivo?->precio ?? $p->precio_venta),
                'incluye_igv'      => (bool) ($precioActivo?->incluye_igv ?? false),
                'imagen'           => $p->imagen_url ?? null,
                'tiene_variantes'  => $variantes->isNotEmpty(),
                'variantes'        => $variantes,
            ];
        });

        // Pagos digitales configurados para la sucursal del usuario
        $sucursal = Sucursal::where('almacen_id', $almacenPredeterminado)->first();
        $pagosConfig = $sucursal
            ? $sucursal->pagos()->where('activo', true)->get()
                ->mapWithKeys(fn($p) => [$p->tipo_pago => [
                    'titular' => $p->titular,
                    'numero'  => $p->numero,
                    'banco'   => $p->banco,
                    'cci'     => $p->cci,
                    'qr_url'  => $p->qr_imagen_path ? asset('storage/' . $p->qr_imagen_path) : null,
                ]])
            : collect();

        return view('ventas.create', compact(
            'clientes', 'productos', 'almacenes', 'categorias', 'almacenPredeterminado', 'pagosConfig'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id'               => 'nullable|exists:clientes,id',
            'almacen_id'               => 'required|exists:almacenes,id',
            'observaciones'            => 'nullable|string',
            'tipo_comprobante'         => 'nullable|in:boleta,factura,cotizacion',
            'guia_remision'            => 'nullable|string|max:100',
            'transportista'            => 'nullable|string|max:150',
            'placa_vehiculo'           => 'nullable|string|max:20',
            'metodo_pago'              => 'nullable|in:efectivo,transferencia,yape,plin,mixto',
            'pagos_detalle'            => 'nullable|array',
            'pagos_detalle.*.metodo'   => 'required_with:pagos_detalle|in:efectivo,transferencia,yape,plin',
            'pagos_detalle.*.monto'    => 'required_with:pagos_detalle|numeric|min:0.01',
            'detalles'                       => 'required|array|min:1',
            'detalles.*.producto_id'         => 'required|exists:productos,id',
            'detalles.*.variante_id'         => 'nullable|exists:producto_variantes,id',
            'detalles.*.cantidad'            => 'required|integer|min:1',
            'detalles.*.precio_unitario'     => 'required|numeric|min:0.01',
            'detalles.*.imeis'               => 'nullable|array',
            'detalles.*.imeis.*.codigo_imei' => 'nullable|string',
        ], [
            'detalles.required' => 'Debe agregar al menos un producto',
        ]);

        $subtotal        = collect($validated['detalles'])->sum(fn($d) => $d['cantidad'] * $d['precio_unitario']);
        $tipoComprobante = $validated['tipo_comprobante'] ?? 'boleta';
        $metodoPago      = $validated['metodo_pago'] ?? null;
        $pago            = $metodoPago ? ['metodo_pago' => $metodoPago] : null;

        $datosVenta = [
            'user_id'          => auth()->id(),
            'cliente_id'       => $validated['cliente_id'] ?? null,
            'almacen_id'       => $validated['almacen_id'],
            'fecha'            => now()->toDateString(),
            'subtotal'         => $subtotal,
            'igv'              => 0,
            'total'            => $subtotal,
            'observaciones'    => $validated['observaciones'] ?? null,
            'tipo_comprobante' => $tipoComprobante,
            'guia_remision'    => $validated['guia_remision'] ?? null,
            'transportista'    => $validated['transportista'] ?? null,
            'placa_vehiculo'   => $validated['placa_vehiculo'] ?? null,
            'pagos_detalle'    => $validated['pagos_detalle'] ?? null,
        ];

        try {
            $service = app(VentaService::class);

            if ($tipoComprobante === 'cotizacion') {
                $venta = $service->crearCotizacion($datosVenta, $validated['detalles']);
                $msg   = 'Cotización guardada exitosamente.';
            } else {
                $venta = $service->crearVenta($datosVenta, $validated['detalles'], $pago);
                $msg   = 'Venta registrada exitosamente.';
            }

            if ($request->wantsJson()) {
                return response()->json(['venta_id' => $venta->id]);
            }

            return redirect()->route('ventas.show', $venta)->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function cotizaciones()
    {
        $user = auth()->user();

        $cotizaciones = Venta::with('vendedor', 'cliente', 'almacen')
            ->where('tipo_comprobante', 'cotizacion')
            ->when($user->almacen_id, fn($q) => $q->where('almacen_id', $user->almacen_id))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total'   => Venta::where('tipo_comprobante', 'cotizacion')
                ->when($user->almacen_id, fn($q) => $q->where('almacen_id', $user->almacen_id))
                ->count(),
            'hoy'     => Venta::where('tipo_comprobante', 'cotizacion')
                ->when($user->almacen_id, fn($q) => $q->where('almacen_id', $user->almacen_id))
                ->whereDate('fecha', today())->count(),
            'monto'   => Venta::where('tipo_comprobante', 'cotizacion')
                ->when($user->almacen_id, fn($q) => $q->where('almacen_id', $user->almacen_id))
                ->sum('total'),
        ];

        return view('ventas.cotizaciones', compact('cotizaciones', 'stats'));
    }

    public function convertir(Request $request, Venta $venta)
    {
        $validated = $request->validate([
            'tipo_comprobante' => 'required|in:boleta,factura',
            'metodo_pago'      => 'required|in:efectivo,transferencia,yape,plin,mixto',
        ]);

        try {
            app(VentaService::class)->convertirAVenta(
                $venta,
                $validated['tipo_comprobante'],
                $validated['metodo_pago']
            );

            return redirect()
                ->route('ventas.show', $venta)
                ->with('success', 'Cotización convertida a ' . $validated['tipo_comprobante'] . ' exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Venta $venta)
    {
        $venta->load('vendedor', 'confirmador', 'cliente', 'almacen', 'sucursal', 'serieComprobante',
            'detalles.producto.categoria', 'detalles.variante.color', 'detalles.imei');

        return view('ventas.show', compact('venta'));
    }

    public function pdf(Request $request, Venta $venta)
    {
        $formato = $request->get('formato', 'a4'); // a4 | ticket

        $venta->load('vendedor', 'cliente', 'almacen', 'sucursal', 'serieComprobante',
            'detalles.producto', 'detalles.variante.color', 'detalles.imei');

        $empresa  = Empresa::first() ?? new Empresa(['razon_social' => config('app.name'), 'ruc' => '']);
        $sucursal = $venta->sucursal ?? Sucursal::where('almacen_id', $venta->almacen_id)->first();
        $pagos    = $sucursal
            ? $sucursal->pagos()->where('activo', true)->get()->keyBy('tipo_pago')
            : collect();

        $view = $formato === 'ticket' ? 'pdf.factura-ticket' : 'pdf.factura-a4';

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'sans-serif',
            'chroot'               => public_path(),
        ])->loadView($view, compact('venta', 'empresa', 'sucursal', 'pagos'));

        if ($formato === 'ticket') {
            // 80mm = 226.77pt ancho, altura dinámica amplia
            $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');
        } else {
            $pdf->setPaper('A4', 'portrait');
        }

        $filename = 'comprobante-' . ($venta->numero_documento ?? $venta->codigo) . '.pdf';
        return $pdf->stream($filename);
    }

    public function confirmarPago(Request $request, Venta $venta)
    {
        $validated = $request->validate([
            'metodo_pago' => 'required|in:efectivo,transferencia,yape,plin,mixto',
        ]);

        try {
            app(VentaService::class)->confirmarPago(
                $venta->id,
                $validated['metodo_pago'],
                auth()->id()
            );

            return redirect()
                ->route('ventas.show', $venta)
                ->with('success', 'Pago confirmado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function imeisDisponibles(Request $request)
    {
        $productoId = $request->input('producto_id');
        $almacenId  = $request->input('almacen_id');
        $varianteId = $request->input('variante_id');

        $imeis = Imei::where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->where('estado_imei', 'en_stock')
            ->when($varianteId, fn($q) => $q->where(function ($inner) use ($varianteId) {
                // Mostrar IMEIs de la variante seleccionada O sin variante asignada (compatibilidad)
                $inner->where('variante_id', $varianteId)->orWhereNull('variante_id');
            }))
            ->get(['id', 'codigo_imei', 'color_id']);

        return response()->json($imeis);
    }
    public function dashboardTienda()
{
    $user = auth()->user();
    
    // Ventas del día actual
    $ventas_dia = Venta::whereDate('fecha', today())
        ->when($user->role->nombre === 'Vendedor', function ($query) use ($user) {
            return $query->where('user_id', $user->id);
        })
        ->sum('total');
    
    // Otras estadísticas que puedas necesitar
    $ventas_pendientes = Venta::where('estado_pago', 'pendiente')
        ->when($user->role->nombre === 'Vendedor', function ($query) use ($user) {
            return $query->where('user_id', $user->id);
        })
        ->count();
    
    $ventas_mes = Venta::whereMonth('fecha', now()->month)
        ->whereYear('fecha', now()->year)
        ->when($user->role->nombre === 'Vendedor', function ($query) use ($user) {
            return $query->where('user_id', $user->id);
        })
        ->sum('total');
    
    $ultimas_ventas = Venta::with('cliente')
        ->when($user->role->nombre === 'Vendedor', function ($query) use ($user) {
            return $query->where('user_id', $user->id);
        })
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    return view('dashboards.tienda', compact(
        'ventas_dia', 
        'ventas_pendientes', 
        'ventas_mes', 
        'ultimas_ventas'
    ));
}
}
