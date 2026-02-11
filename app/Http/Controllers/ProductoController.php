<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Almacen;
use App\Models\MovimientoInventario;
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
                ->except(['index', 'show', 'consultaCajero', 'buscarAjax']);

        
        // Solo Admin puede eliminar
        $this->middleware('role:Administrador')->only(['destroy']);
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
        $categorias = Categoria::activas()->orderBy('nombre')->get();
        $almacenes = Almacen::activos()->orderBy('nombre')->get();
        
        return view('inventario.productos.create', compact('categorias', 'almacenes'));
    }

    /**
     * Guardar nuevo producto
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'unidad_medida' => 'required|string|max:20',
            'codigo_barras' => 'nullable|string|max:100|unique:productos,codigo_barras',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'precio_compra_actual' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'precio_mayorista' => 'nullable|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'stock_maximo' => 'required|integer|min:1',
            'ubicacion' => 'nullable|string|max:50',
            'estado' => 'required|in:activo,inactivo,descontinuado',
            // Para stock inicial
            'stock_inicial' => 'nullable|integer|min:0',
            'almacen_id' => 'required_with:stock_inicial|exists:almacenes,id',
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio',
            'categoria_id.required' => 'Debe seleccionar una categoría',
            'precio_compra_actual.required' => 'El precio de compra es obligatorio',
            'precio_venta.required' => 'El precio de venta es obligatorio',
            'stock_minimo.required' => 'El stock mínimo es obligatorio',
            'stock_maximo.required' => 'El stock máximo es obligatorio',
        ]);

        // Generar código automático
        $validated['codigo'] = Producto::generarCodigo();

        // Subir imagen si existe
        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        // Crear producto
        $producto = Producto::create($validated);

        // Si hay stock inicial, registrar movimiento
        if ($request->filled('stock_inicial') && $request->stock_inicial > 0) {
            MovimientoInventario::registrarMovimiento([
                'producto_id' => $producto->id,
                'almacen_id' => $request->almacen_id,
                'tipo_movimiento' => 'ingreso',
                'cantidad' => $request->stock_inicial,
                'motivo' => 'Stock inicial del producto',
            ]);
        }

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
            'precio_compra_actual' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'precio_mayorista' => 'nullable|numeric|min:0',
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
    public function consultaCajero(Request $request)
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
    $categorias = Categoria::activas()->orderBy('nombre')->get();
    
    return view('inventario.consulta-cajero', compact('productos', 'categorias'));
}

}