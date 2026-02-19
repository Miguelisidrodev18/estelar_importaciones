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
use App\Services\CodigoBarrasService;
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
    $categorias = Categoria::where('estado', 'activo')
        ->with(['marcas' => fn($q) => $q->where('estado', 'activo')])
        ->orderBy('nombre')
        ->get();
    $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();
    
    // Obtener datos del catálogo
    $marcas = Marca::where('estado', 'activo')->orderBy('nombre')->get();
    $modelos = Modelo::where('estado', 'activo')->with('marca')->orderBy('nombre')->get();
    $colores = Color::where('estado', 'activo')->orderBy('nombre')->get(); // ✅ YA LO TIENES
    $unidades = UnidadMedida::where('estado', 'activo')->orderBy('nombre')->get(); // ✅ YA LO TIENES
    
    return view('inventario.productos.create', compact(
        'categorias',
        'almacenes',
        'marcas',
        'modelos',
        'colores',      // ✅ BIEN
        'unidades'      // ✅ BIEN
    ));
}

    /**
     * Guardar nuevo producto
     */
    public function store(Request $request, CodigoBarrasService $codigoBarrasService)
{
    $validated = $request->validate([
        // Campos básicos
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'categoria_id' => 'required|exists:categorias,id',
        
        // ✅ NUEVO: Foreign Keys desde catálogos
        'marca_id' => 'nullable|exists:marcas,id',           // Cambiado de 'marca'
        'modelo_id' => 'nullable|exists:modelos,id',         // Cambiado de 'modelo'
        'color_id' => 'nullable|exists:colores,id',          // ✅ NUEVO
        'unidad_medida_id' => 'required|exists:unidades_medida,id', // Cambiado de 'unidad_medida'
        
        // ✅ NUEVO: Tipo de inventario (reemplaza a tipo_producto)
        'tipo_inventario' => 'required|in:cantidad,serie',   // cantidad = accesorio, serie = celular
        
        // ✅ NUEVO: Garantía (para celulares)
        'dias_garantia' => 'required_if:tipo_inventario,serie|integer|min:0',
        'tipo_garantia' => 'required_if:tipo_inventario,serie|in:proveedor,tienda,fabricante',
        
        // Códigos
        'codigo_barras' => 'nullable|string|max:50|unique:productos,codigo_barras',
        
        // Stock
        'stock_minimo' => 'required|integer|min:0',
        'stock_maximo' => 'required|integer|min:1',
        'ubicacion' => 'nullable|string|max:100',
        
        // Imagen y estado
        'imagen' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        'estado' => 'required|in:activo,inactivo,descontinuado',
        
        // Stock inicial (solo para accesorios)
        'stock_inicial' => 'nullable|integer|min:0',
        'almacen_id' => 'nullable|required_with:stock_inicial|exists:almacenes,id',
        
        // ✅ ELIMINAR estos campos (ya no van en productos)
        // 'tipo_producto' - reemplazado por tipo_inventario
        // 'precio_venta' - se maneja en ventas
        // 'precio_mayorista' - se maneja en ventas
        // 'unidad_medida' - reemplazado por unidad_medida_id
    ]);

    // Generar código automático si no existe
    if (empty($request->codigo)) {
        $validated['codigo'] = Producto::generarCodigo();
    }

    // Generar código de barras si no se proporcionó
    if (empty($validated['codigo_barras'])) {
        $tipoBarras = ($validated['tipo_inventario'] ?? 'cantidad') === 'serie' ? 'celular' : 'accesorio';
        $validated['codigo_barras'] = $codigoBarrasService->generarCodigoUnico(null, $tipoBarras);
    }

    // Manejar imagen
    if ($request->hasFile('imagen')) {
        $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
    }

    \DB::transaction(function () use ($validated, $request) {
        // Crear producto
        $producto = Producto::create($validated);
        
        \Log::info('Producto creado:', [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'tipo_inventario' => $producto->tipo_inventario
        ]);
        
        // Stock inicial SOLO para productos tipo 'cantidad' (accesorios)
        if ($producto->tipo_inventario === 'cantidad' && 
            $request->filled('stock_inicial') && 
            $request->stock_inicial > 0) {
            
            if ($request->filled('almacen_id')) {
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
        
        // Si tiene código de barras, guardarlo también en la nueva tabla
        if ($producto->codigo_barras) {
            $producto->codigosBarras()->create([
                'codigo_barras' => $producto->codigo_barras,
                'descripcion' => 'Principal',
                'es_principal' => true
            ]);
        }
    });

    return redirect()
        ->route('inventario.productos.index')
        ->with('success', 'Producto creado exitosamente');
}
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
        // Cargar relaciones necesarias
        $producto->load(['marca', 'modelo', 'color', 'categoria', 'unidadMedida']);
        
        // Obtener datos para los selects
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre')->get();
        $marcas = \App\Models\Catalogo\Marca::where('estado', 'activo')->orderBy('nombre')->get();
        $modelos = \App\Models\Catalogo\Modelo::where('estado', 'activo')
                    ->with('marca')
                    ->orderBy('nombre')
                    ->get();
        
        // Obtener colores y unidades de medida
        $colores = \App\Models\Catalogo\Color::where('estado', 'activo')
                    ->orderBy('nombre')
                    ->get();
        
        $unidades = \App\Models\Catalogo\UnidadMedida::where('estado', 'activo')
                    ->orderBy('nombre')
                    ->get();

        // Obtener códigos de barras (opcional)
        $codigosBarras = $producto->codigosBarras ?? collect();

        return view('inventario.productos.edit', compact(
            'producto', 
            'categorias', 
            'marcas', 
            'modelos',
            'colores',
            'unidades',
            'codigosBarras'
        ));
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
        
        // ✅ NUEVO: Foreign Keys
        'marca_id' => 'nullable|exists:marcas,id',
        'modelo_id' => 'nullable|exists:modelos,id',
        'color_id' => 'nullable|exists:colores,id',
        'unidad_medida_id' => 'required|exists:unidades_medida,id',
        
        // ✅ NUEVO: Tipo de inventario
        'tipo_inventario' => 'required|in:cantidad,serie',
        
        // ✅ NUEVO: Garantía
        'dias_garantia' => 'required_if:tipo_inventario,serie|integer|min:0',
        'tipo_garantia' => 'required_if:tipo_inventario,serie|in:proveedor,tienda,fabricante',
        
        // Código de barras (único excepto este producto)
        'codigo_barras' => 'nullable|string|max:100|unique:productos,codigo_barras,' . $producto->id,
        
        // Imagen
        'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        
        // Stock
        'stock_minimo' => 'required|integer|min:0',
        'stock_maximo' => 'required|integer|min:1',
        'ubicacion' => 'nullable|string|max:50',
        'estado' => 'required|in:activo,inactivo,descontinuado',
        
        // ❌ ELIMINAR: 'unidad_medida', 'tipo_producto'
    ]);

    // Subir nueva imagen si existe
    if ($request->hasFile('imagen')) {
        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }
        $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
    }

    // Actualizar producto
    $producto->update($validated);
    
    // Actualizar código de barras principal si cambió
    if ($producto->wasChanged('codigo_barras')) {
        // Buscar si ya tiene un código principal
        $principal = $producto->codigosBarras()->where('es_principal', true)->first();
        
        if ($principal) {
            // Actualizar el existente
            $principal->update(['codigo_barras' => $producto->codigo_barras]);
        } else {
            // Crear nuevo
            $producto->codigosBarras()->create([
                'codigo_barras' => $producto->codigo_barras,
                'descripcion' => 'Principal',
                'es_principal' => true
            ]);
        }
    }

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
     * Mostrar gestión de códigos de barras del producto
     */
    public function codigosBarras(Producto $producto)
    {
        $codigosBarras = $producto->codigosBarras()->orderBy('es_principal', 'desc')->get();
        
        return view('inventario.productos.codigos-barras', compact('producto', 'codigosBarras'));
    }

    /**
     * Guardar nuevo código de barras
     */
    public function storeCodigoBarras(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'codigo_barras' => 'required|string|max:50|unique:productos_codigos_barras,codigo_barras',
            'descripcion' => 'nullable|string|max:100',
            'es_principal' => 'boolean'
        ]);

        // Si es principal, quitar principal de los demás
        if ($request->boolean('es_principal')) {
            $producto->codigosBarras()->update(['es_principal' => false]);
        }

        $producto->codigosBarras()->create($validated);

        return redirect()->back()->with('success', 'Código de barras agregado');
    }

    /**
     * Eliminar código de barras
     */
    public function destroyCodigoBarras($codigoBarrasId)
    {
        $codigoBarras = \App\Models\ProductoCodigoBarras::findOrFail($codigoBarrasId);
        $codigoBarras->delete();

        return redirect()->back()->with('success', 'Código de barras eliminado');
    }

    /**
 * Generar código de barras automático (AJAX)
 */
public function generarCodigoBarras(Request $request, CodigoBarrasService $codigoBarrasService)
{
    try {
        $request->validate([
            'tipo' => 'nullable|in:celular,accesorio,otros'
        ]);

        $tipo = $request->get('tipo', 'otros');

        $codigo = $codigoBarrasService->generarCodigoUnico(null, $tipo);
        
        return response()->json([
            'success' => true,
            'codigo' => $codigo,
            'message' => 'Código generado correctamente'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error generando código de barras', [
            'error' => $e->getMessage(),
            'tipo' => $request->tipo
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error al generar código: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Validar si un código de barras ya existe (AJAX)
 */
public function validarCodigoBarras(Request $request)
    {
        $codigo = $request->get('codigo');
        $productoId = $request->get('producto_id');
        
        $query = Producto::where('codigo_barras', $codigo);
        
        // Si estamos editando, excluir el producto actual
        if ($productoId) {
            $query->where('id', '!=', $productoId);
        }
        
        $existe = $query->exists();
        
        // También verificar en la tabla de códigos de barras múltiples
        if (!$existe && class_exists('\App\Models\ProductoCodigoBarras')) {
            $existe = \App\Models\ProductoCodigoBarras::where('codigo_barras', $codigo)->exists();
        }
        
        return response()->json([
            'existe' => $existe,
            'codigo' => $codigo
        ]);
    }
    /**
     * Establecer como principal
     */
    public function setPrincipalCodigoBarras($codigoBarrasId)
    {
        $codigoBarras = \App\Models\ProductoCodigoBarras::findOrFail($codigoBarrasId);
        
        // Quitar principal de todos los demás
        $codigoBarras->producto->codigosBarras()->update(['es_principal' => false]);
        
        // Establecer este como principal
        $codigoBarras->update(['es_principal' => true]);

        return redirect()->back()->with('success', 'Código de barras principal actualizado');
    }
    /**
     * Búsqueda AJAX para autocompletado
     */
    public function buscarAjax(Request $request)
{
    $termino = $request->get('q');
    $proveedor_id = $request->get('proveedor_id'); // Para filtrar por proveedor
    
    $query = Producto::activos()
        ->with(['marca', 'modelo', 'color', 'unidadMedida'])
        ->buscar($termino);
    
    // Si se especifica proveedor, filtrar productos que tiene ese proveedor
    if ($proveedor_id) {
        $query->whereHas('proveedores', function($q) use ($proveedor_id) {
            $q->where('proveedor_id', $proveedor_id);
        });
    }
    
    $productos = $query->limit(10)->get([
        'id', 
        'codigo', 
        'nombre', 
        'tipo_inventario',
        'marca_id',
        'modelo_id',
        'color_id',
        'unidad_medida_id'
    ]);
    
    // Formatear respuesta para el select2
    $resultados = $productos->map(function($producto) {
        return [
            'id' => $producto->id,
            'text' => $producto->codigo . ' - ' . $producto->nombre,
            'nombre' => $producto->nombre,
            'codigo' => $producto->codigo,
            'tipo_inventario' => $producto->tipo_inventario,
            'marca' => $producto->marca->nombre ?? '',
            'modelo' => $producto->modelo->nombre ?? '',
            'color' => $producto->color->nombre ?? '',
            'unidad' => $producto->unidadMedida->nombre ?? ''
        ];
    });
    
    return response()->json($resultados);
}
/**
 * Mostrar productos de un proveedor específico
 */
public function productosPorProveedor($proveedorId)
{
    $proveedor = \App\Models\Proveedor::findOrFail($proveedorId);
    
    $productos = Producto::whereHas('proveedores', function($q) use ($proveedorId) {
        $q->where('proveedor_id', $proveedorId);
    })->with(['marca', 'modelo', 'color'])->get();
    
    return view('proveedores.productos', compact('proveedor', 'productos'));
}

/**
 * Asociar producto a proveedor
 */
public function asociarProveedor(Request $request, $productoId)
{
    $validated = $request->validate([
        'proveedor_id' => 'required|exists:proveedores,id',
        'codigo_proveedor' => 'nullable|string|max:100',
        'plazo_entrega_dias' => 'nullable|integer|min:0',
        'es_preferente' => 'boolean'
    ]);
    
    $producto = Producto::findOrFail($productoId);
    
    $producto->proveedores()->syncWithoutDetaching([
        $validated['proveedor_id'] => [
            'codigo_proveedor' => $validated['codigo_proveedor'] ?? null,
            'plazo_entrega_dias' => $validated['plazo_entrega_dias'] ?? 0,
            'es_preferente' => $validated['es_preferente'] ?? false
        ]
    ]);
    
    return response()->json(['success' => true]);
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
        
        if ($producto->tipo_inventario !== 'serie') {
            return response()->json(['error' => 'El producto no es tipo serie/celular'], 400);
        }

        $query = \App\Models\Imei::where('producto_id', $productoId)
                                  ->where('almacen_id', $almacenId);

        // Filtrar según tipo de movimiento
        switch ($tipoMovimiento) {
            case 'salida':
            case 'transferencia':
            case 'merma':
                $query->where('estado_imei', 'en_stock');
                break;
            case 'devolucion':
                $query->where('estado_imei', 'vendido');
                break;
            case 'ajuste':
                // Mostrar todos
                break;
            default:
                $query->where('estado_imei', 'en_stock');
                break;
        }

        $imeis = $query->limit(100)
                       ->get(['id', 'codigo_imei', 'serie', 'color_id', 'estado_imei']);
        
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