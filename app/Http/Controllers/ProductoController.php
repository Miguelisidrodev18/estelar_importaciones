<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Almacen;
use App\Models\Catalogo\Marca;
use App\Models\Catalogo\Modelo;
use App\Models\Catalogo\Color;
use App\Models\Catalogo\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    /**
     * Constructor - Definir permisos por rol
     */
    public function __construct()
    {
        // Solo Admin y Almacenero pueden crear/editar
        $this->middleware('role:Administrador,Almacenero')
                ->except(['index', 'show', 'consultaTienda', 'buscarAjax']);

        
        // Solo Admin puede eliminar
        $this->middleware('role:Administrador')->only(['destroy']);
    }
    public function consultaTienda(Request $request)
{
    $query = Producto::with('categoria')->activos();
    
    // Búsqueda simple
    if ($request->filled('buscar')) {
        $query->buscar($request->buscar);
    }
    
    // Filtro por categoría
    if ($request->filled('categoria_id')) {
        $query->where('categoria_id', $request->categoria_id);
    }
    
    $productos = $query->orderBy('nombre')->paginate(20);
    $categorias = \App\Models\Categoria::activas()->orderBy('nombre')->get();
    
    return view('inventario.consulta-tienda', compact('productos', 'categorias'));
}
    /**
     * Mostrar listado de productos con filtros
     */
    public function index(Request $request)
    {
        $query = Producto::with('categoria');
        
        // Filtro por búsqueda
        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }
        
        // Filtro por categoría
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }
        
        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        // Filtro por estado de stock
        if ($request->filled('stock_estado')) {
            switch ($request->stock_estado) {
                case 'bajo':
                    $query->stockBajo();
                    break;
                case 'sin_stock':
                    $query->sinStock();
                    break;
            }
        }
        
        $productos = $query->orderBy('nombre')->paginate(15);
        $categorias = Categoria::activas()->orderBy('nombre')->get();
        
        // Verificar permisos
        $canCreate = in_array(auth()->user()->role->nombre, ['Administrador', 'Almacenero']);
        $canEdit = in_array(auth()->user()->role->nombre, ['Administrador', 'Almacenero']);
        $canDelete = auth()->user()->role->nombre === 'Administrador';
        
        return view('inventario.productos.index', compact('productos', 'categorias', 'canCreate', 'canEdit', 'canDelete'));
    }

    /**
     * Mostrar formulario de crear producto
     */
    public function create()
    {
        // Verificar que los modelos existen
        if (!class_exists('App\Models\Catalogo\Marca')) {
            dd('El modelo Marca no existe');
        }
        // Obtener datos de inventario
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre')->get();
        $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();
        
        // Obtener datos del catálogo
        $marcas = Marca::where('estado', 'activo')->orderBy('nombre')->get();
        $modelos = Modelo::where('estado', 'activo')->with('marca')->orderBy('nombre')->get();
        $colores = Color::where('estado', 'activo')->orderBy('nombre')->get();
        $unidades = UnidadMedida::where('estado', 'activo')->orderBy('nombre')->get();
        
        return view('inventario.productos.create', compact(
            'categorias',
            'almacenes',
            'marcas',
            'modelos',
            'colores',
            'unidades'
        ));
    }
    

    /**
     * Guardar nuevo producto
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'nombre' => 'required|string|max:255',
        'tipo_producto' => 'required|in:celular,accesorio', // ✅ ASEGURAR QUE ESTÉ AQUÍ
        'categoria_id' => 'required|exists:categorias,id',
        'marca' => 'nullable|string|max:100',
        'modelo' => 'nullable|string|max:100',
        'descripcion' => 'nullable|string',
        'codigo_barras' => 'nullable|string|max:50|unique:productos,codigo_barras',
        'unidad_medida' => 'required|in:unidad,caja,paquete',
        'stock_minimo' => 'required|integer|min:0',
        'stock_maximo' => 'required|integer|min:1',
        'ubicacion' => 'nullable|string|max:100',
        'imagen' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        'estado' => 'required|in:activo,inactivo,descontinuado',
        'stock_inicial' => 'nullable|integer|min:0',
        'almacen_id' => 'nullable|exists:almacenes,id',
    ]);

    // 🔍 DEBUG: Ver qué tipo_producto llegó
    \Log::info('Tipo producto recibido:', ['tipo' => $request->tipo_producto]);
    \Log::info('Validated data:', $validated);

    // Generar código automático
    $validated['codigo'] = Producto::generarCodigo();

    // Manejar imagen
    if ($request->hasFile('imagen')) {
        $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
    }

    \DB::transaction(function () use ($validated, $request) {
        // ✅ CREAR PRODUCTO CON TODOS LOS DATOS VALIDADOS
        $producto = Producto::create($validated);
        
        \Log::info('Producto creado:', [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'tipo_producto' => $producto->tipo_producto // Verificar qué se guardó
        ]);
        
        // Solo crear stock inicial para ACCESORIOS
        if ($producto->tipo_producto === 'accesorio' && $request->filled('stock_inicial') && $request->stock_inicial > 0) {
            if ($request->filled('almacen_id')) {
                // Crear movimiento de stock inicial
                \App\Models\MovimientoInventario::registrarMovimiento([
                    'producto_id' => $producto->id,
                    'almacen_id' => $request->almacen_id,
                    'tipo_movimiento' => 'ingreso',
                    'cantidad' => $request->stock_inicial,
                    'motivo' => 'Stock inicial del producto',
                    'usuario_id' => auth()->id(),
                ]);
            }
        }
        
        // Para CELULARES, el stock se manejará por IMEIs
    });

    return redirect()
        ->route('inventario.productos.index')
        ->with('success', 'Producto creado exitosamente');
}
    /**
     * Mostrar un producto específico
     */
    public function show(Producto $producto)
    {
        $producto->load(['categoria', 'movimientos' => function($query) {
            $query->latest()->limit(10);
        }]);
        
        return view('inventario.productos.show', compact('producto'));
    }

    /**
     * Mostrar formulario de editar producto
     */
    public function edit(Producto $producto)
    {
        $categorias = Categoria::activas()->orderBy('nombre')->get();
        
        return view('inventario.productos.edit', compact('producto', 'categorias'));
    }

    /**
     * Actualizar producto
     */
    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'unidad_medida' => 'required|string|max:20',
            'codigo_barras' => 'nullable|string|max:100|unique:productos,codigo_barras,' . $producto->id,
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'stock_minimo' => 'required|integer|min:0',
            'stock_maximo' => 'required|integer|min:1',
            'ubicacion' => 'nullable|string|max:50',
            'estado' => 'required|in:activo,inactivo,descontinuado',
        ]);

        // Subir nueva imagen si existe
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto->update($validated);

        return redirect()
            ->route('inventario.productos.index')
            ->with('success', 'Producto actualizado exitosamente');
    }

    /**
     * Eliminar producto
     */
    public function destroy(Producto $producto)
    {
        try {
            // Eliminar imagen si existe
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            
            $producto->delete();
            
            return redirect()
                ->route('inventario.productos.index')
                ->with('success', 'Producto eliminado exitosamente');
                
        } catch (\Exception $e) {
            return redirect()
                ->route('inventario.productos.index')
                ->with('error', 'No se puede eliminar el producto porque tiene movimientos registrados');
        }
    }

    /**
     * Búsqueda AJAX para autocompletado
     */
    public function buscarAjax(Request $request)
    {
        $termino = $request->get('q');
        
        $productos = Producto::activos()
            ->buscar($termino)
            ->limit(10)
            ->get(['id', 'codigo', 'nombre', 'precio_venta', 'stock_actual']);
        
        return response()->json($productos);
    }
    /**
 * API: Obtener IMEIs disponibles de un producto en un almacén
 */
public function getImeisDisponibles(Request $request)
{
    try {
        $productoId = $request->get('producto_id');
        $almacenId = $request->get('almacen_id');
        $tipoMovimiento = $request->get('tipo_movimiento');
        
        // Log para debug
        \Log::info('getImeisDisponibles llamado', [
            'producto_id' => $productoId,
            'almacen_id' => $almacenId,
            'tipo_movimiento' => $tipoMovimiento
        ]);
        
        if (!$productoId || !$almacenId) {
            return response()->json(['error' => 'Faltan parámetros'], 400);
        }
        
        // Verificar que el producto existe y es tipo celular
        $producto = \App\Models\Producto::find($productoId);
        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
        
        if ($producto->tipo_producto !== 'celular') {
            return response()->json(['error' => 'El producto no es tipo celular'], 400);
        }
        
        $query = \App\Models\Imei::where('producto_id', $productoId)
                                  ->where('almacen_id', $almacenId);
        
        // Filtrar según tipo de movimiento
        switch ($tipoMovimiento) {
            case 'salida':
            case 'transferencia':
            case 'merma':
                $query->where('estado', 'disponible');
                break;
            case 'devolucion':
                $query->where('estado', 'vendido');
                break;
            case 'ajuste':
                // Mostrar todos
                break;
            default:
                // Si no hay tipo de movimiento, mostrar disponibles
                $query->where('estado', 'disponible');
                break;
        }
        
        $imeis = $query->limit(100)
                       ->get(['id', 'codigo_imei', 'serie', 'color', 'estado']);
        
        \Log::info('IMEIs encontrados', ['count' => $imeis->count()]);
        
        return response()->json($imeis);
        
    } catch (\Exception $e) {
        \Log::error('Error en getImeisDisponibles', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => 'Error interno del servidor',
            'message' => $e->getMessage()
        ], 500);
    }
}
}