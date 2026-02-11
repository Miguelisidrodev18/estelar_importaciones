<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\User;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    /**
     * Constructor - Solo Admin y Almacenero
     */
    public function __construct()
    {
        $this->middleware('role:Administrador,Almacenero');
    }

    /**
     * Mostrar listado de almacenes
     */
    public function index(Request $request)
    {
        $query = Almacen::with('encargado');
        
        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        $almacenes = $query->orderBy('nombre')->paginate(15);
        
        // Estadísticas
        $stats = [
            'total' => Almacen::count(),
            'activos' => Almacen::activos()->count(),
            'principal' => Almacen::principal()->count(),
            'sucursales' => Almacen::sucursales()->count(),
        ];
        
        // Verificar permisos
        $canCreate = in_array(auth()->user()->role->nombre, ['Administrador', 'Almacenero']);
        $canEdit = in_array(auth()->user()->role->nombre, ['Administrador', 'Almacenero']);
        $canDelete = auth()->user()->role->nombre === 'Administrador';
        
        return view('inventario.almacenes.index', compact('almacenes', 'stats', 'canCreate', 'canEdit', 'canDelete'));
    }

    /**
     * Mostrar formulario para crear almacén
     */
    public function create()
    {
        // Obtener usuarios que pueden ser encargados (Admin, Almacenero)
        $usuarios = User::whereHas('role', function($query) {
            $query->whereIn('nombre', ['Administrador', 'Almacenero']);
        })->orderBy('name')->get();
        
        return view('inventario.almacenes.create', compact('usuarios'));
    }

    /**
     * Guardar nuevo almacén
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'encargado_id' => 'nullable|exists:users,id',
            'tipo' => 'required|in:principal,sucursal,temporal',
            'estado' => 'required|in:activo,inactivo',
        ], [
            'nombre.required' => 'El nombre del almacén es obligatorio',
            'tipo.required' => 'Debe seleccionar el tipo de almacén',
        ]);

        // Generar código automático
        $validated['codigo'] = Almacen::generarCodigo();

        Almacen::create($validated);

        return redirect()
            ->route('inventario.almacenes.index')
            ->with('success', 'Almacén creado exitosamente');
    }

    /**
     * Mostrar detalle de un almacén con su stock
     */
    public function show(Almacen $almacen)
    {
        $almacen->load(['encargado', 'movimientos' => function($query) {
            $query->with('producto')->latest()->limit(20);
        }]);
        
        // Obtener stock por producto en este almacén
        $stockPorProducto = \DB::table('movimientos_inventario')
            ->select('producto_id', \DB::raw('SUM(CASE 
                WHEN tipo_movimiento IN ("ingreso", "devolucion") THEN cantidad
                WHEN tipo_movimiento IN ("salida", "merma") THEN -cantidad
                WHEN tipo_movimiento = "transferencia" AND almacen_id = ' . $almacen->id . ' THEN -cantidad
                WHEN tipo_movimiento = "transferencia" AND almacen_destino_id = ' . $almacen->id . ' THEN cantidad
                ELSE 0
            END) as stock_almacen'))
            ->where(function($query) use ($almacen) {
                $query->where('almacen_id', $almacen->id)
                      ->orWhere('almacen_destino_id', $almacen->id);
            })
            ->groupBy('producto_id')
            ->having('stock_almacen', '>', 0)
            ->get();
        
        // Cargar información de productos
        $productosIds = $stockPorProducto->pluck('producto_id');
        $productos = \App\Models\Producto::whereIn('id', $productosIds)->get()->keyBy('id');
        
        // Combinar información
        $stockDetalle = $stockPorProducto->map(function($item) use ($productos) {
            $producto = $productos->get($item->producto_id);
            return [
                'producto' => $producto,
                'stock' => $item->stock_almacen,
            ];
        })->sortByDesc('stock');
        
        return view('inventario.almacenes.show', compact('almacen', 'stockDetalle'));
    }

    /**
     * Mostrar formulario para editar almacén
     */
    public function edit(Almacen $almacen)
    {
        $usuarios = User::whereHas('role', function($query) {
            $query->whereIn('nombre', ['Administrador', 'Almacenero']);
        })->orderBy('name')->get();
        
        return view('inventario.almacenes.edit', compact('almacen', 'usuarios'));
    }

    /**
     * Actualizar almacén
     */
    public function update(Request $request, Almacen $almacen)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'encargado_id' => 'nullable|exists:users,id',
            'tipo' => 'required|in:principal,sucursal,temporal',
            'estado' => 'required|in:activo,inactivo',
        ]);

        $almacen->update($validated);

        return redirect()
            ->route('inventario.almacenes.index')
            ->with('success', 'Almacén actualizado exitosamente');
    }

    /**
     * Eliminar almacén
     */
    public function destroy(Almacen $almacen)
    {
        try {
            $almacen->delete();
            
            return redirect()
                ->route('inventario.almacenes.index')
                ->with('success', 'Almacén eliminado exitosamente');
                
        } catch (\Exception $e) {
            return redirect()
                ->route('inventario.almacenes.index')
                ->with('error', 'No se puede eliminar el almacén porque tiene movimientos registrados');
        }
    }
}