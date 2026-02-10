<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Almacen;
use Illuminate\Http\Request;

class MovimientoInventarioController extends Controller
{
    /**
     * Constructor - Solo Admin y Almacenero
     */
    public function __construct()
    {
        $this->middleware('role:Administrador,Almacenero');
    }

    /**
     * Mostrar historial de movimientos con filtros
     */
    public function index(Request $request)
    {
        $query = MovimientoInventario::with(['producto', 'almacen', 'usuario']);
        
        // Filtro por tipo de movimiento
        if ($request->filled('tipo_movimiento')) {
            $query->where('tipo_movimiento', $request->tipo_movimiento);
        }
        
        // Filtro por producto
        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }
        
        // Filtro por almacén
        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->almacen_id);
        }
        
        // Filtro por fecha
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }
        
        // Ordenar por más reciente
        $movimientos = $query->latest()->paginate(20);
        
        // Datos para filtros
        $productos = Producto::activos()->orderBy('nombre')->get(['id', 'codigo', 'nombre']);
        $almacenes = Almacen::activos()->orderBy('nombre')->get(['id', 'codigo', 'nombre']);
        
        // Estadísticas
        $stats = [
            'total_movimientos' => MovimientoInventario::count(),
            'movimientos_hoy' => MovimientoInventario::hoy()->count(),
            'ingresos_hoy' => MovimientoInventario::hoy()->ingresos()->count(),
            'salidas_hoy' => MovimientoInventario::hoy()->salidas()->count(),
        ];
        
        return view('inventario.movimientos.index', compact('movimientos', 'productos', 'almacenes', 'stats'));
    }

    /**
     * Mostrar formulario para crear movimiento
     */
    public function create()
    {
        $productos = Producto::activos()->orderBy('nombre')->get();
        $almacenes = Almacen::activos()->orderBy('nombre')->get();
        
        return view('inventario.movimientos.create', compact('productos', 'almacenes'));
    }

    /**
     * Guardar nuevo movimiento
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'tipo_movimiento' => 'required|in:ingreso,salida,ajuste,transferencia,devolucion,merma',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
            'almacen_destino_id' => 'required_if:tipo_movimiento,transferencia|exists:almacenes,id',
        ], [
            'producto_id.required' => 'Debe seleccionar un producto',
            'almacen_id.required' => 'Debe seleccionar un almacén',
            'tipo_movimiento.required' => 'Debe seleccionar el tipo de movimiento',
            'cantidad.required' => 'La cantidad es obligatoria',
            'cantidad.min' => 'La cantidad debe ser mayor a 0',
            'motivo.required' => 'El motivo es obligatorio',
            'almacen_destino_id.required_if' => 'Debe seleccionar un almacén destino para transferencias',
        ]);

        try {
            // Registrar el movimiento usando el método del modelo
            MovimientoInventario::registrarMovimiento([
                'producto_id' => $request->producto_id,
                'almacen_id' => $request->almacen_id,
                'tipo_movimiento' => $request->tipo_movimiento,
                'cantidad' => $request->cantidad,
                'motivo' => $request->motivo,
                'observaciones' => $request->observaciones,
                'almacen_destino_id' => $request->almacen_destino_id,
            ]);

            return redirect()
                ->route('inventario.movimientos.index')
                ->with('success', 'Movimiento registrado exitosamente. Stock actualizado.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al registrar movimiento: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de un movimiento
     */
    public function show(MovimientoInventario $movimiento)
    {
        $movimiento->load(['producto', 'almacen', 'almacenDestino', 'usuario']);
        
        return view('inventario.movimientos.show', compact('movimiento'));
    }

    /**
     * API: Obtener stock actual de un producto
     */
    public function getStockActual(Request $request)
    {
        $productoId = $request->get('producto_id');
        
        if (!$productoId) {
            return response()->json(['error' => 'Producto no especificado'], 400);
        }
        
        $producto = Producto::find($productoId);
        
        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
        
        return response()->json([
            'stock_actual' => $producto->stock_actual,
            'unidad_medida' => $producto->unidad_medida,
            'codigo' => $producto->codigo,
            'nombre' => $producto->nombre,
        ]);
    }
}