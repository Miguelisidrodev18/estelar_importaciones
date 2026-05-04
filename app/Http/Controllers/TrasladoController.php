<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\StockAlmacen;
use App\Models\Imei;
use App\Models\GuiaRemision;
use App\Models\Empresa;
use App\Services\TrasladoService;
use App\Services\SunatService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TrasladoController extends Controller
{
    public function index()
    {
        // Agrupar por numero_guia para mostrar un traslado por grupo
        $todos = MovimientoInventario::with('producto', 'almacen', 'almacenDestino', 'usuario')
            ->where('tipo_movimiento', 'transferencia')
            ->orderBy('created_at', 'desc')
            ->get();

        // Agrupar: key = numero_guia o "id:{id}" si no tiene guía
        $traslados = $todos->groupBy(fn($m) => $m->numero_guia ?: "id:{$m->id}");

        return view('traslados.index', compact('traslados'));
    }

    public function create(Request $request)
    {
        $productos = Producto::where('estado', 'activo')->orderBy('nombre')->get();

        // Cargar almacenes con la serie de guía de remisión (tipo 09) de su sucursal
        $almacenes = Almacen::where('estado', 'activo')
            ->with(['sucursal.series' => fn($q) => $q->where('tipo_comprobante', '09')->where('activo', true)])
            ->orderBy('nombre')
            ->get();

        // Mapa almacen_id → datos de la serie de guía para el JS
        $guiaSeriesMap = $almacenes->mapWithKeys(function ($alm) {
            $serie = $alm->sucursal?->series->first();
            if (!$serie) return [$alm->id => null];
            return [$alm->id => [
                'serie_id' => $serie->id,
                'numero'   => $serie->serie . '-' . str_pad($serie->correlativo_actual, 8, '0', STR_PAD_LEFT),
            ]];
        })->filter();

        // Stock por producto y almacén (productos accesorio)
        $stocksData = StockAlmacen::all()
            ->groupBy('producto_id')
            ->map(fn($rows) => $rows->pluck('cantidad', 'almacen_id'));

        // Conteo de IMEIs en_stock por producto y almacén
        $imeisData = Imei::where('estado_imei', Imei::ESTADO_EN_STOCK)
            ->selectRaw('producto_id, almacen_id, COUNT(*) as total')
            ->groupBy('producto_id', 'almacen_id')
            ->get()
            ->groupBy('producto_id')
            ->map(fn($rows) => $rows->pluck('total', 'almacen_id'));

        // tipo_inventario por producto
        $tiposInventario = $productos->pluck('tipo_inventario', 'id');

        // Último conductor registrado para pre-rellenar el formulario
        $ultimoConductor = GuiaRemision::whereNotNull('conductor_dni')
            ->whereNotNull('conductor_nombre')
            ->latest()
            ->first();

        return view('traslados.create', compact(
            'productos', 'almacenes', 'stocksData', 'imeisData', 'tiposInventario', 'ultimoConductor', 'guiaSeriesMap'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'almacen_id'              => 'required|exists:almacenes,id',
            'almacen_destino_id'      => 'required|exists:almacenes,id|different:almacen_id',
            'numero_guia'             => 'nullable|string|max:50',
            'guia_serie_id'           => 'nullable|exists:series_comprobantes,id',
            'transportista'           => 'nullable|string|max:255',
            'observaciones'           => 'nullable|string',
            'productos'               => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad'    => 'nullable|integer|min:1',
            'productos.*.imei_ids'    => 'nullable|array',
            'productos.*.imei_ids.*'  => 'nullable|exists:imeis,id',
            // Guía de remisión
            'guia.motivo_traslado'       => 'required|string|max:50',
            'guia.modalidad'             => 'required|in:privado,publico',
            'guia.fecha_traslado'        => 'required|date',
            'guia.peso_total'            => 'nullable|numeric|min:0',
            'guia.bultos'                => 'nullable|integer|min:1',
            'guia.direccion_partida'     => 'nullable|string|max:300',
            'guia.ubigeo_partida'        => 'nullable|string|max:6',
            'guia.direccion_llegada'     => 'nullable|string|max:300',
            'guia.ubigeo_llegada'        => 'nullable|string|max:6',
            'guia.transportista_tipo_doc'=> 'nullable|string|max:10',
            'guia.transportista_doc'     => 'nullable|string|max:15',
            'guia.transportista_nombre'  => 'nullable|string|max:200',
            'guia.conductor_dni'         => 'nullable|string|max:8',
            'guia.conductor_nombre'      => 'nullable|string|max:200',
            'guia.conductor_licencia'    => 'nullable|string|max:20',
            'guia.placa_vehiculo'        => 'nullable|string|max:20',
        ], [
            'almacen_destino_id.different'    => 'El almacén destino debe ser diferente al origen.',
            'productos.required'              => 'Debe agregar al menos un producto.',
            'productos.min'                   => 'Debe agregar al menos un producto.',
            'guia.motivo_traslado.required'   => 'El motivo de traslado es obligatorio.',
            'guia.modalidad.required'         => 'La modalidad de transporte es obligatoria.',
            'guia.fecha_traslado.required'    => 'La fecha de traslado es obligatoria.',
        ]);

        try {
            $guiaData    = $validated['guia'] ?? null;
            $trasladoData = $validated;
            unset($trasladoData['guia']);

            $numeroGuia = app(TrasladoService::class)->crearTraslado(
                array_merge($trasladoData, ['user_id' => auth()->id()])
            );

            if ($guiaData) {
                GuiaRemision::create(array_merge(['numero_guia' => $numeroGuia], $guiaData));
            }

            // Incrementar correlativo de la serie usada
            if (!empty($validated['guia_serie_id'])) {
                \App\Models\SerieComprobante::where('id', $validated['guia_serie_id'])->increment('correlativo_actual');
            }

            return redirect()
                ->route('traslados.index')
                ->with('success', "Traslado registrado. Guía: {$numeroGuia}");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function guiaPdf(MovimientoInventario $traslado)
    {
        $guia = GuiaRemision::where('numero_guia', $traslado->numero_guia)->first();

        if (!$guia) {
            abort(404, 'Este traslado no tiene guía de remisión.');
        }

        $todosProductos = MovimientoInventario::with(['producto.unidadMedida', 'imeisTrasladados.imei', 'almacen', 'almacenDestino'])
            ->where('numero_guia', $traslado->numero_guia)
            ->where('tipo_movimiento', 'transferencia')
            ->orderBy('id')
            ->get();

        $empresa = Empresa::first();

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'DejaVu Sans',
            'chroot'               => public_path(),
        ])->loadView('pdf.guia-remision-traslado', compact('traslado', 'todosProductos', 'guia', 'empresa'));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('guia-' . $traslado->numero_guia . '.pdf');
    }

    public function show(MovimientoInventario $traslado)
    {
        $traslado->load('producto', 'almacen', 'almacenDestino', 'usuario', 'usuarioConfirma', 'imeisTrasladados.imei');

        // Cargar todos los productos del mismo traslado (mismo numero_guia)
        $todosProductos = $traslado->numero_guia
            ? MovimientoInventario::with(['producto', 'imeisTrasladados.imei'])
                ->where('numero_guia', $traslado->numero_guia)
                ->where('tipo_movimiento', 'transferencia')
                ->orderBy('id')
                ->get()
            : collect([$traslado]);

        $guia = $traslado->numero_guia
            ? GuiaRemision::where('numero_guia', $traslado->numero_guia)->first()
            : null;

        return view('traslados.show', compact('traslado', 'todosProductos', 'guia'));
    }

    public function pendientes()
    {
        $movimientos = MovimientoInventario::with([
                'producto',
                'almacen',
                'almacenDestino',
                'usuario',
                'imeisTrasladados.imei',
            ])
            ->where('tipo_movimiento', 'transferencia')
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->get();

        // Agrupar por numero_guia
        $grupos = $movimientos->groupBy(fn($m) => $m->numero_guia ?: "id:{$m->id}");

        return view('traslados.pendientes', compact('grupos'));
    }

    public function stock(Request $request)
    {
        $query = Producto::with(['categoria'])->where('estado', 'activo');

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }
        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->buscar . '%')
                  ->orWhere('codigo', 'like', '%' . $request->buscar . '%');
            });
        }

        $productos    = $query->orderBy('nombre')->paginate(25)->withQueryString();
        $productoIds  = $productos->pluck('id');

        $stocksPorProducto = StockAlmacen::whereIn('producto_id', $productoIds)
            ->get()
            ->groupBy('producto_id')
            ->map(fn($rows) => $rows->keyBy('almacen_id'));

        $imeisPorProducto = Imei::whereIn('producto_id', $productoIds)
            ->where('estado_imei', Imei::ESTADO_EN_STOCK)
            ->selectRaw('producto_id, almacen_id, COUNT(*) as total')
            ->groupBy('producto_id', 'almacen_id')
            ->get()
            ->groupBy('producto_id')
            ->map(fn($rows) => $rows->pluck('total', 'almacen_id'));

        foreach ($productos as $producto) {
            if ($producto->tipo_inventario === 'serie') {
                $imeiMap         = $imeisPorProducto[$producto->id] ?? collect();
                $producto->stocks   = $imeiMap->mapWithKeys(fn($total, $aid) => [$aid => (object)['cantidad' => $total]]);
                $producto->es_serie = true;
            } else {
                $producto->stocks   = $stocksPorProducto[$producto->id] ?? collect();
                $producto->es_serie = false;
            }
        }

        $almacenes  = Almacen::where('estado', 'activo')->orderBy('nombre')->get();
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre')->get();

        return view('traslados.stock', compact('productos', 'almacenes', 'categorias'));
    }

    public function confirmar(Request $request, MovimientoInventario $traslado)
    {
        try {
            app(TrasladoService::class)->confirmarRecepcion(
                $traslado->id,
                auth()->id()
            );

            return redirect()
                ->route('traslados.pendientes')
                ->with('success', 'Traslado confirmado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * AJAX: IMEIs en_stock para un producto + almacén origen.
     */
    public function imeisDisponibles(Request $request)
    {
        try {
            $request->validate([
                'producto_id' => 'required|exists:productos,id',
                'almacen_id'  => 'required|exists:almacenes,id',
            ]);

            $imeis = Imei::where('producto_id', $request->producto_id)
                ->where('almacen_id', $request->almacen_id)
                ->where('estado_imei', Imei::ESTADO_EN_STOCK)
                ->orderBy('codigo_imei')
                ->get(['id', 'codigo_imei', 'serie']);

            return response()->json($imeis);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error'   => 'Parámetros inválidos.',
                'details' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Error al cargar IMEIs: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function buscarRuc(string $ruc): JsonResponse
    {
        $result = app(SunatService::class)->consultarRuc($ruc);

        if (!($result['success'] ?? false)) {
            return response()->json([
                'error' => $result['message'] ?? 'RUC no encontrado. Ingrese el nombre manualmente.',
            ], 404);
        }

        return response()->json(['nombre' => $result['data']['razon_social'] ?? null]);
    }
}
