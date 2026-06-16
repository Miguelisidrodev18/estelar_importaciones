<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\GuiaRemision;
use App\Models\Imei;
use App\Models\MovimientoInventario;
use App\Models\StockAlmacen;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevolucionController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Administrador,Almacenero,Vendedor');
    }

    public function index(Request $request)
    {
        $query = MovimientoInventario::query()
            ->selectRaw('
                numero_guia,
                MIN(id)           AS id,
                MIN(almacen_id)   AS almacen_id,
                MIN(user_id)      AS user_id,
                MIN(created_at)   AS created_at,
                MIN(observaciones) AS observaciones,
                COUNT(*)          AS total_items,
                SUM(cantidad)     AS total_cantidad
            ')
            ->where('tipo_movimiento', 'devolucion')
            ->groupBy('numero_guia');

        if ($request->filled('buscar')) {
            $q = $request->buscar;
            $query->where(function ($w) use ($q) {
                $w->where('numero_guia', 'like', "%{$q}%")
                  ->orWhere('documento_referencia', 'like', "%{$q}%")
                  ->orWhere('observaciones', 'like', "%{$q}%")
                  ->orWhereHas('producto', fn($p) => $p->where('nombre', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->almacen_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $devoluciones = $query->orderByRaw('MIN(created_at) DESC')
            ->paginate(20)
            ->withQueryString();

        // Cargar nombres sin eager loading (grupos ya aggregados)
        $almacenIds   = $devoluciones->pluck('almacen_id')->filter()->unique();
        $userIds      = $devoluciones->pluck('user_id')->filter()->unique();
        $almacenesMap = Almacen::whereIn('id', $almacenIds)->pluck('nombre', 'id');
        $usersMap     = \App\Models\User::whereIn('id', $userIds)->pluck('name', 'id');

        // Stats globales (sin filtros)
        $stats = [
            'total_guias'    => MovimientoInventario::where('tipo_movimiento', 'devolucion')
                                    ->distinct('numero_guia')->count('numero_guia'),
            'total_unidades' => MovimientoInventario::where('tipo_movimiento', 'devolucion')
                                    ->sum('cantidad'),
            'hoy'            => MovimientoInventario::where('tipo_movimiento', 'devolucion')
                                    ->whereDate('created_at', today())
                                    ->distinct('numero_guia')->count('numero_guia'),
        ];

        $almacenes = Almacen::activos()->orderBy('nombre')->get();

        return view('devoluciones.index', compact(
            'devoluciones', 'almacenes', 'almacenesMap', 'usersMap', 'stats'
        ));
    }

    public function create(Request $request)
    {
        $clientes = Cliente::activos()->orderBy('nombre')->get();
        $almacenes = Almacen::activos()->orderBy('nombre')->get();

        // Si viene cliente_id, cargamos sus ventas con detalles
        $ventas = collect();
        $clienteId = $request->input('cliente_id');
        if ($clienteId) {
            $ventas = Venta::with(['detalles.producto', 'detalles.variante', 'detalles.imei'])
                ->where('cliente_id', $clienteId)
                ->where('estado_pago', 'pagado')
                ->orderBy('fecha', 'desc')
                ->get();
        }

        return view('devoluciones.create', compact('clientes', 'almacenes', 'ventas', 'clienteId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id'      => 'required|exists:clientes,id',
            'almacen_id'      => 'required|exists:almacenes,id',
            'detalle_ids'     => 'required|array|min:1',
            'detalle_ids.*'   => 'required|exists:detalle_ventas,id',
            'observaciones'   => 'nullable|string',
            // Guía de remisión
            'guia.motivo_traslado'   => 'nullable|string|max:50',
            'guia.modalidad'         => 'nullable|in:privado,publico',
            'guia.fecha_traslado'    => 'nullable|date',
            'guia.direccion_partida' => 'nullable|string|max:300',
            'guia.ubigeo_partida'    => 'nullable|string|max:6',
            'guia.direccion_llegada' => 'nullable|string|max:300',
            'guia.ubigeo_llegada'    => 'nullable|string|max:6',
            'guia.conductor_dni'     => 'nullable|string|max:8',
            'guia.conductor_nombre'  => 'nullable|string|max:200',
            'guia.conductor_licencia'=> 'nullable|string|max:20',
            'guia.placa_vehiculo'    => 'nullable|string|max:20',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $almacenId   = $validated['almacen_id'];
                $numeroGuia  = 'DEV-' . strtoupper(uniqid());
                $guiaCreada  = false;

                foreach ($validated['detalle_ids'] as $detalleId) {
                    $detalle = DetalleVenta::with(['producto', 'imei', 'variante'])->findOrFail($detalleId);

                    $stockAnterior = StockAlmacen::obtenerOCrear($detalle->producto_id, $almacenId)->cantidad;

                    // Crear movimiento de devolución
                    MovimientoInventario::create([
                        'producto_id'        => $detalle->producto_id,
                        'variante_id'        => $detalle->variante_id,
                        'almacen_id'         => $almacenId,
                        'user_id'            => auth()->id(),
                        'tipo_movimiento'    => 'devolucion',
                        'cantidad'           => $detalle->cantidad,
                        'stock_anterior'     => $stockAnterior,
                        'stock_nuevo'        => $stockAnterior + $detalle->cantidad,
                        'motivo'             => 'Devolución de cliente',
                        'documento_referencia' => $detalle->venta?->codigo,
                        'numero_guia'        => $numeroGuia,
                        'observaciones'      => $validated['observaciones'] ?? null,
                        'estado'             => 'confirmado',
                    ]);

                    // Para productos tipo serie: devolver el IMEI a stock
                    if ($detalle->imei_id && $detalle->imei) {
                        $detalle->imei->update([
                            'estado_imei' => Imei::ESTADO_EN_STOCK,
                            'almacen_id'  => $almacenId,
                        ]);

                        // Recalcular stock_actual del producto desde conteo real de IMEIs
                        $totalStock = Imei::where('producto_id', $detalle->producto_id)
                            ->where('estado_imei', Imei::ESTADO_EN_STOCK)
                            ->count();
                        $detalle->producto->update(['stock_actual' => $totalStock]);

                        // Sincronizar variante si existe
                        if ($detalle->variante_id && $detalle->variante) {
                            $varianteStock = Imei::where('producto_id', $detalle->producto_id)
                                ->where('variante_id', $detalle->variante_id)
                                ->where('estado_imei', Imei::ESTADO_EN_STOCK)
                                ->count();
                            $detalle->variante->update(['stock_actual' => $varianteStock]);
                        }
                    } else {
                        // Para productos por cantidad: incrementar stock en almacén
                        StockAlmacen::obtenerOCrear($detalle->producto_id, $almacenId)
                            ->incrementar($detalle->cantidad);

                        if ($detalle->variante) {
                            // incrementarStock() sincroniza Producto.stock_actual desde suma de variantes
                            $detalle->variante->incrementarStock($detalle->cantidad);
                        } else {
                            // Sin variante: recalcular desde SUM(StockAlmacen) para consistencia
                            $totalStock = StockAlmacen::where('producto_id', $detalle->producto_id)->sum('cantidad');
                            $detalle->producto->update(['stock_actual' => $totalStock]);
                        }
                    }

                    $guiaCreada = true;
                }

                // Crear Guía de Remisión si hay datos
                if ($guiaCreada && !empty($validated['guia']['fecha_traslado'])) {
                    GuiaRemision::create(array_merge(
                        ['numero_guia' => $numeroGuia, 'motivo_traslado' => 'DEVOLUCION'],
                        $validated['guia'] ?? []
                    ));
                }
            });

            return redirect()
                ->route('devoluciones.index')
                ->with('success', 'Devolución registrada exitosamente.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al registrar la devolución: ' . $e->getMessage());
        }
    }

    public function show(MovimientoInventario $devolucion)
    {
        $devolucion->load('producto', 'almacen', 'usuario');

        $todosMovimientos = MovimientoInventario::with(['producto'])
            ->where('numero_guia', $devolucion->numero_guia)
            ->where('tipo_movimiento', 'devolucion')
            ->get();

        $guia = GuiaRemision::where('numero_guia', $devolucion->numero_guia)->first();

        return view('devoluciones.show', compact('devolucion', 'todosMovimientos', 'guia'));
    }
}
