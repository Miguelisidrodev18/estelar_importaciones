<?php

namespace App\Http\Controllers;

use App\Models\Imei;
use App\Models\Producto;
use App\Models\Almacen;
use App\Models\Catalogo\Color;
use Illuminate\Http\Request;

class ImeiController extends Controller
{
    /**
     * Constructor - Solo Admin y Almacenero
     */
    public function __construct()
    {
        $this->middleware('role:Administrador,Almacenero');
    }

    /**
     * Mostrar listado de IMEIs
     */
    public function index(Request $request)
    {
        $query = Imei::with(['producto', 'almacen', 'color']);
        
        // Filtro por búsqueda (IMEI o Serie)
        if ($request->filled('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where('codigo_imei', 'like', '%' . $request->buscar . '%')
                    ->orWhere('serie', 'like', '%' . $request->buscar . '%');
            });
        }
        
        // Filtro por producto
        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }
        
        // Filtro por almacén
        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->almacen_id);
        }
        
        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        $imeis = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Estadísticas
        $stats = [
            'total' => Imei::count(),
            'disponibles' => Imei::disponibles()->count(),
            'vendidos' => Imei::vendidos()->count(),
            'reservados' => Imei::where('estado', 'reservado')->count(),
        ];
        
        // Para los filtros
        $productos = Producto::where('tipo_producto', 'celular')->activos()->orderBy('nombre')->get();
        $almacenes = Almacen::activos()->orderBy('nombre')->get();
        
        return view('inventario.imeis.index', compact('imeis', 'stats', 'productos', 'almacenes'));
    }

    /**
     * Mostrar formulario para crear IMEI
     */
    public function create()
    {
        // Cargar productos tipo CELULAR activos con marca y modelo (relaciones FK)
        $productos = Producto::with('marca', 'modelo')
            ->where('tipo_producto', 'celular')
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $colores = Color::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $almacenes = Almacen::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('inventario.imeis.create', compact('productos', 'colores', 'almacenes'));
    }

    /**
     * Guardar nuevo IMEI
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_imei' => 'required|string|size:15|unique:imeis,codigo_imei',
            'producto_id' => 'required|exists:productos,id',
            'almacen_id'  => 'required|exists:almacenes,id',
            'color_id'    => 'nullable|exists:colores,id',
            'serie'       => 'nullable|string|max:50',
            'estado'      => 'required|in:disponible,reservado,dañado',
        ], [
            'codigo_imei.required' => 'El código IMEI es obligatorio',
            'codigo_imei.size'     => 'El IMEI debe tener exactamente 15 dígitos',
            'codigo_imei.unique'   => 'Este código IMEI ya está registrado',
            'producto_id.required' => 'Debe seleccionar un producto',
            'almacen_id.required'  => 'Debe seleccionar un almacén',
        ]);

        // Verificar que el producto sea tipo celular
        $producto = Producto::findOrFail($validated['producto_id']);
        if ($producto->tipo_producto !== 'celular') {
            return back()->withErrors(['producto_id' => 'Solo se pueden registrar IMEIs para productos tipo celular']);
        }

        \DB::transaction(function () use ($validated, $producto) {
            // Crear IMEI
            Imei::create($validated);
            
            // Incrementar stock del producto
            $producto->increment('stock_actual');
            
            // Incrementar stock en almacén
            \App\Models\StockAlmacen::obtenerOCrear($validated['producto_id'], $validated['almacen_id'])
                ->incrementar(1);
        });

        return redirect()
            ->route('inventario.imeis.index')
            ->with('success', 'IMEI registrado exitosamente');
    }

    /**
     * Mostrar detalle de un IMEI
     */
    public function show(Imei $imei)
    {
        $imei->load(['producto', 'almacen', 'movimientos' => function($query) {
            $query->with('usuario')->latest()->limit(10);
        }]);
        
        return view('inventario.imeis.show', compact('imei'));
    }

    /**
     * Mostrar formulario para editar IMEI
     */
    public function edit(Imei $imei)
    {
        $almacenes = Almacen::activos()->orderBy('nombre')->get();
        
        return view('inventario.imeis.edit', compact('imei', 'almacenes'));
    }

    /**
     * Actualizar IMEI
     */
    public function update(Request $request, Imei $imei)
    {
        $validated = $request->validate([
            'almacen_id' => 'required|exists:almacenes,id',
            'color_id'   => 'nullable|exists:colores,id',
            'serie'      => 'nullable|string|max:50',
            'estado'     => 'required|in:disponible,vendido,reservado,dañado,garantia',
        ]);

        $imei->update($validated);

        return redirect()
            ->route('inventario.imeis.index')
            ->with('success', 'IMEI actualizado exitosamente');
    }

    /**
     * API: Obtener IMEIs disponibles por producto y almacén
     */
    public function getImeisDisponibles(Request $request)
    {
        $productoId = $request->get('producto_id');
        $almacenId = $request->get('almacen_id');
        
        $imeis = Imei::where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->disponibles()
            ->get(['id', 'codigo_imei', 'serie', 'color']);
        
        return response()->json($imeis);
    }
}