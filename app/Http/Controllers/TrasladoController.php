<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\StockAlmacen;
use App\Models\Imei;
use App\Services\TrasladoService;
use Illuminate\Http\Request;

class TrasladoController extends Controller
{
    public function index()
    {
        $traslados = MovimientoInventario::with('producto', 'almacen', 'almacenDestino', 'usuario')
            ->where('tipo_movimiento', 'transferencia')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('traslados.index', compact('traslados'));
    }

    public function create(Request $request)
    {
        $productos = Producto::where('estado', 'activo')->orderBy('nombre')->get();
        $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();

        // Stock por producto y almacén (productos cantidad)
        $stocksData = StockAlmacen::all()
            ->groupBy('producto_id')
            ->map(fn($rows) => $rows->pluck('cantidad', 'almacen_id'));

        // IMEIs en_stock por producto y almacén (productos serie)
        $imeisData = Imei::where('estado_imei', 'en_stock')
            ->selectRaw('producto_id, almacen_id, COUNT(*) as total')
            ->groupBy('producto_id', 'almacen_id')
            ->get()
            ->groupBy('producto_id')
            ->map(fn($rows) => $rows->pluck('total', 'almacen_id'));

        // Tipo de inventario por producto
        $tiposInventario = $productos->pluck('tipo_inventario', 'id');

        $selectedProductoId = $request->input('producto_id');

        return view('traslados.create', compact(
            'productos', 'almacenes', 'stocksData', 'imeisData', 'tiposInventario', 'selectedProductoId'
        ));
    }

    public function store(Request $request)
    {
        $productoId = $request->input('producto_id');
        $producto   = Producto::find($productoId);
        $esSerie    = $producto && $producto->tipo_inventario === 'serie';

        $rules = [
            'producto_id'        => 'required|exists:productos,id',
            'almacen_id'         => 'required|exists:almacenes,id',
            'almacen_destino_id' => 'required|exists:almacenes,id|different:almacen_id',
            'numero_guia'        => 'nullable|string|max:50|unique:movimientos_inventario,numero_guia',
            'transportista'      => 'nullable|string|max:255',
            'observaciones'      => 'nullable|string',
        ];

        if ($esSerie) {
            $rules['imei_ids']   = 'required|array|min:1';
            $rules['imei_ids.*'] = 'required|exists:imeis,id';
        } else {
            $rules['cantidad'] = 'required|integer|min:1';
        }

        $validated = $request->validate($rules, [
            'almacen_destino_id.different' => 'El almacén destino debe ser diferente al origen',
            'imei_ids.required'            => 'Debe seleccionar al menos un IMEI para trasladar.',
            'imei_ids.min'                 => 'Debe seleccionar al menos un IMEI para trasladar.',
        ]);

        try {
            $movimiento = app(TrasladoService::class)->crearTraslado(
                array_merge($validated, ['user_id' => auth()->id()])
            );

            return redirect()
                ->route('traslados.index')
                ->with('success', "Traslado creado. Guía: {$movimiento->numero_guia}");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(MovimientoInventario $traslado)
    {
        $traslado->load('producto', 'almacen', 'almacenDestino', 'usuario', 'usuarioConfirma', 'imei', 'imeisTrasladados.imei');

        return view('traslados.show', compact('traslado'));
    }

    public function pendientes()
    {
        $traslados = MovimientoInventario::with([
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

        return view('traslados.pendientes', compact('traslados'));
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

        $productos = $query->orderBy('nombre')->paginate(25)->withQueryString();
        $productoIds = $productos->pluck('id');

        // Stock por almacén (productos cantidad)
        $stocksPorProducto = StockAlmacen::whereIn('producto_id', $productoIds)
            ->get()
            ->groupBy('producto_id')
            ->map(fn($rows) => $rows->keyBy('almacen_id'));

        // Conteo de IMEIs en_stock por almacén (productos serie)
        $imeisPorProducto = Imei::whereIn('producto_id', $productoIds)
            ->where('estado_imei', 'en_stock')
            ->selectRaw('producto_id, almacen_id, COUNT(*) as total')
            ->groupBy('producto_id', 'almacen_id')
            ->get()
            ->groupBy('producto_id')
            ->map(fn($rows) => $rows->pluck('total', 'almacen_id'));

        foreach ($productos as $producto) {
            if ($producto->tipo_inventario === 'serie') {
                $imeiMap = $imeisPorProducto[$producto->id] ?? collect();
                $producto->stocks = $imeiMap->mapWithKeys(fn($total, $almacenId) => [
                    $almacenId => (object)['cantidad' => $total],
                ]);
                $producto->es_serie = true;
            } else {
                $producto->stocks = $stocksPorProducto[$producto->id] ?? collect();
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
                ->with('success', 'Traslado confirmado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * AJAX: devuelve IMEIs en_stock para un producto y almacén.
     */
    public function imeisDisponibles(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'almacen_id'  => 'required|exists:almacenes,id',
        ]);

        $imeis = Imei::where('producto_id', $request->producto_id)
            ->where('almacen_id', $request->almacen_id)
            ->where('estado_imei', 'en_stock')
            ->orderBy('codigo_imei')
            ->get(['id', 'codigo_imei', 'serie']);

        return response()->json($imeis);
    }
}
