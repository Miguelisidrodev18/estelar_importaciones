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
        
        if ($request->filled('tipo_movimiento')) {
            $query->where('tipo_movimiento', $request->tipo_movimiento);
        }
        
        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
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
        
        $movimientos = $query->latest()->paginate(20);
        
        $productos = Producto::activos()->orderBy('nombre')->get(['id', 'codigo', 'nombre']);
        $almacenes = Almacen::activos()->orderBy('nombre')->get(['id', 'codigo', 'nombre']);
        
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
        \Log::info('📥 Datos recibidos del formulario:', $request->all());
        
        // =====================================================
        // FIX 1: Validación condicional según tipo de producto
        // =====================================================
        $producto = Producto::find($request->producto_id);
        $esCelular = $producto && $producto->tipo_producto === 'celular';
        
        $rules = [
            'producto_id' => 'required|exists:productos,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'tipo_movimiento' => 'required|in:ingreso,salida,ajuste,transferencia,devolucion,merma',
            'motivo' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
            'almacen_destino_id' => 'nullable|exists:almacenes,id',
            'numero_guia' => 'nullable|string|max:50',
        ];
        
        // FIX 2: Si es celular, IMEI es obligatorio y cantidad no se valida como required
        if ($esCelular) {
            $rules['imei_id'] = 'required|exists:imeis,id';
            $rules['cantidad'] = 'nullable|integer|min:1';
        } else {
            $rules['imei_id'] = 'nullable|exists:imeis,id';
            $rules['cantidad'] = 'required|integer|min:1';
        }
        
        // FIX 3: Almacén destino solo obligatorio en transferencias
        if ($request->tipo_movimiento === 'transferencia') {
            $rules['almacen_destino_id'] = 'required|exists:almacenes,id|different:almacen_id';
            $rules['numero_guia'] = 'required|string|max:50';
        }

        $messages = [
            'producto_id.required' => 'Debe seleccionar un producto.',
            'almacen_id.required' => 'Debe seleccionar un almacén.',
            'tipo_movimiento.required' => 'Debe seleccionar el tipo de movimiento.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'motivo.required' => 'El motivo es obligatorio.',
            'imei_id.required' => 'Debe seleccionar un IMEI para productos tipo celular.',
            'almacen_destino_id.required' => 'Debe seleccionar un almacén destino para transferencias.',
            'almacen_destino_id.different' => 'El almacén destino debe ser diferente al almacén origen.',
            'numero_guia.required' => 'El número de guía es obligatorio para transferencias.',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            // Preparar datos para el modelo
            $datos = [
                'producto_id' => $request->producto_id,
                'almacen_id' => $request->almacen_id,
                'tipo_movimiento' => $request->tipo_movimiento,
                'imei_id' => $request->imei_id,
                'cantidad' => $esCelular ? 1 : $request->cantidad,
                'motivo' => $request->motivo,
                'observaciones' => $request->observaciones,
                'almacen_destino_id' => $request->almacen_destino_id,
                'numero_guia' => $request->numero_guia,
                'user_id' => auth()->id(),
            ];
            
            MovimientoInventario::registrarMovimiento($datos);

            return redirect()
                ->route('inventario.movimientos.index')
                ->with('success', 'Movimiento registrado exitosamente.');

        } catch (\Exception $e) {
            \Log::error('❌ Error en store:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
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

    /**
     * API: Obtener IMEIs disponibles de un producto en un almacén
     */
    public function getImeisDisponibles(Request $request)
    {
        try {
            $productoId = $request->get('producto_id');
            $almacenId = $request->get('almacen_id');
            $tipoMovimiento = $request->get('tipo_movimiento', 'salida');
            
            if (!$productoId || !$almacenId) {
                return response()->json(['error' => 'Faltan parámetros requeridos'], 400);
            }
            
            $producto = Producto::find($productoId);
            if (!$producto) {
                return response()->json(['error' => 'Producto no encontrado'], 404);
            }
            
            if ($producto->tipo_producto !== 'celular') {
                return response()->json(['error' => 'El producto seleccionado no es tipo celular'], 400);
            }
            
            $query = \App\Models\Imei::where('producto_id', $productoId)
                                    ->where('almacen_id', $almacenId);
            
            switch ($tipoMovimiento) {
                case 'salida':
                case 'transferencia':
                case 'merma':
                    $query->where('estado', 'disponible');
                    break;
                case 'devolucion':
                    $query->where('estado', 'vendido');
                    break;
                case 'ingreso':
                    return response()->json(['error' => 'Los ingresos de celulares se registran en Compras'], 400);
                case 'ajuste':
                    break;
                default:
                    $query->where('estado', 'disponible');
                    break;
            }
            
            $imeis = $query->select('id', 'codigo_imei', 'serie', 'color', 'estado')
                            ->orderBy('codigo_imei')
                            ->limit(200)
                            ->get();
            
            return response()->json($imeis);
            
        } catch (\Exception $e) {
            \Log::error('Error en getImeisDisponibles:', [
                'message' => $e->getMessage(),
                'producto_id' => $request->get('producto_id'),
                'almacen_id' => $request->get('almacen_id'),
            ]);
            
            return response()->json([
                'error' => 'Error interno del servidor',
                'message' => config('app.debug') ? $e->getMessage() : 'Error al cargar IMEIs'
            ], 500);
        }
    }
}