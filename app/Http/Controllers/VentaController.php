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
            ->with(['categoria', 'variantesActivas.color'])
            ->orderBy('nombre')
            ->get()
            ->map(function($p) {
                $variantes = $p->variantesActivas->map(fn($v) => [
                    'id'              => $v->id,
                    'sku'             => $v->sku,
                    'color_id'        => $v->color_id,
                    'color_nombre'    => $v->color?->nombre,
                    'color_hex'       => $v->color?->codigo_hex,
                    'capacidad'       => $v->capacidad,
                    'sobreprecio'     => (float)$v->sobreprecio,
                    'stock_actual'    => (int)$v->stock_actual,
                    'nombre_completo' => $v->nombre_completo,
                    'tiene_stock'     => $v->tieneStock(),
                ]);
                return [
                    'id'              => $p->id,
                    'nombre'          => $p->nombre,
                    'codigo'          => $p->codigo,
                    'codigo_barras'   => $p->codigo_barras ?? null,
                    'categoria_id'    => $p->categoria_id,
                    'tipo_inventario' => $p->tipo_inventario,
                    'stock_actual'    => (int) $p->stock_actual,
                    'precio_venta'    => (float) $p->precio_venta,
                    'imagen'          => $p->imagen_url ?? null,
                    'tiene_variantes' => $variantes->isNotEmpty(),
                    'variantes'       => $variantes,
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
            'detalles'                 => 'required|array|min:1',
            'detalles.*.producto_id'   => 'required|exists:productos,id',
            'detalles.*.variante_id'   => 'nullable|exists:producto_variantes,id',
            'detalles.*.cantidad'      => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0.01',
            'detalles.*.imei_id'       => 'nullable|exists:imeis,id',
        ], [
            'detalles.required' => 'Debe agregar al menos un producto',
        ]);

        $subtotal        = collect($validated['detalles'])->sum(fn($d) => $d['cantidad'] * $d['precio_unitario']);
        $tipoComprobante = $validated['tipo_comprobante'] ?? 'boleta';
        $metodoPago      = $validated['metodo_pago'] ?? null;
        $pago            = $metodoPago ? ['metodo_pago' => $metodoPago] : null;

        try {
            $venta = app(VentaService::class)->crearVenta(
                [
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
                ],
                $validated['detalles'],
                $pago
            );

            if ($request->wantsJson()) {
                return response()->json(['venta_id' => $venta->id]);
            }

            return redirect()
                ->route('ventas.show', $venta)
                ->with('success', 'Venta registrada exitosamente.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', $e->getMessage());
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

        $pdf = Pdf::loadView($view, compact('venta', 'empresa', 'sucursal', 'pagos'));

        if ($formato === 'ticket') {
            // 80mm = 226.77pt ancho, altura dinámica amplia
            $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');
        } else {
            $pdf->setPaper('A4', 'portrait');
        }

        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'sans-serif']);

        $filename = 'comprobante-' . ($venta->numero_documento ?? $venta->codigo) . '.pdf';
        return $pdf->stream($filename);
    }

    public function confirmarPago(Request $request, Venta $venta)
    {
        $validated = $request->validate([
            'metodo_pago' => 'required|in:efectivo,transferencia,yape,plin',
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
            ->when($varianteId, fn($q) => $q->where('variante_id', $varianteId))
            ->get(['id', 'codigo_imei', 'color']);

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
