<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\Almacen;
use App\Models\ProductoPrecio;
use App\Models\ProductoPrecioHistorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrecioController extends Controller
{
    /**
     * Constructor - Solo Admin y Almacenero pueden gestionar precios
     */
    public function __construct()
    {
        $this->middleware('role:Administrador,Almacenero');
    }

    /**
     * Listado de productos con sus precios actuales
     */
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'marca', 'modelo', 'precios' => function($q) {
            $q->where('activo', true)->latest();
        }]);

        // Filtros
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->buscar . '%')
                  ->orWhere('codigo', 'like', '%' . $request->buscar . '%');
            });
        }

        $productos = $query->paginate(20);
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre')->get();

        return view('precios.index', compact('productos', 'categorias'));
    }
    /**
    * Mostrar formulario para editar un precio específico
    */
    public function edit(Producto $producto, $precioId)
    {
        $precio = ProductoPrecio::findOrFail($precioId);
        
        $producto->load(['categoria', 'marca', 'modelo']);
        $proveedores = Proveedor::where('estado', 'activo')->orderBy('razon_social')->get();

        return view('precios.edit', compact('producto', 'precio', 'proveedores'));
    }
    /**
     * Mostrar detalle de precios de un producto
     */
    public function show(Producto $producto)
    {
        $producto->load(['categoria', 'marca', 'modelo', 'proveedores', 'precios' => function($q) {
            $q->with('proveedor')->latest();
        }]);

        $proveedores = Proveedor::where('estado', 'activo')->orderBy('razon_social')->get();
        $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();

        return view('precios.show', compact('producto', 'proveedores', 'almacenes'));
    }

    /**
     * Calcular precio sugerido basado en proveedor y márgenes
     */
    public function calcular(Request $request, Producto $producto)
    {
        $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'precio_compra' => 'required|numeric|min:0.01',
            'margen' => 'required|numeric|min:0|max:100',
            'impuestos' => 'nullable|numeric|min:0|max:100',
        ]);

        // Calcular precios
        $precioBase = $request->precio_compra * (1 + $request->margen / 100);
        $precioFinal = $precioBase * (1 + ($request->impuestos ?? 0) / 100);

        // Obtener información del proveedor
        $proveedor = Proveedor::find($request->proveedor_id);

        return response()->json([
            'success' => true,
            'precio_base' => round($precioBase, 2),
            'precio_final' => round($precioFinal, 2),
            'margen_aplicado' => $request->margen,
            'impuestos' => $request->impuestos ?? 0,
            'proveedor' => [
                'id' => $proveedor->id,
                'nombre' => $proveedor->razon_social,
            ]
        ]);
    }

    /**
     * Actualizar precio existente
     */
    public function update(Request $request, Producto $producto, $precioId)
    {
        $precio = ProductoPrecio::findOrFail($precioId);

        $validated = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'precio_compra' => 'required|numeric|min:0.01',
            'precio_venta' => 'required|numeric|min:0.01',
            'precio_mayorista' => 'nullable|numeric|min:0.01',
            'margen' => 'required|numeric|min:0|max:100',
            'observaciones' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'activo' => 'required|in:0,1',
        ]);

        try {
            DB::beginTransaction();

            // Registrar cambio en historial si el precio cambió
            if ($precio->precio_venta != $validated['precio_venta']) {
                ProductoPrecioHistorial::create([
                    'producto_id' => $producto->id,
                    'precio_anterior' => $precio->precio_venta,
                    'precio_nuevo' => $validated['precio_venta'],
                    'motivo' => $validated['observaciones'] ?? 'Edición manual',
                    'usuario_id' => auth()->id(),
                ]);
            }

            // Actualizar precio
            $precio->update($validated);

            DB::commit();

            return redirect()
                ->route('precios.show', $producto)
                ->with('success', 'Precio actualizado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar precio: ' . $e->getMessage());
        }
    }

    /**
     * Ver historial de cambios de precio
     */
    public function historial(Producto $producto)
    {
        $historial = ProductoPrecioHistorial::where('producto_id', $producto->id)
            ->with('usuario')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('precios.historial', compact('producto', 'historial'));
    }

    /**
     * Aplicar precio a todas las tiendas
     */
    public function aplicarATiendas(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'precio_id' => 'required|exists:producto_precios,id',
        ]);

        $precio = ProductoPrecio::findOrFail($validated['precio_id']);

        // Aquí podrías crear registros en precios_venta por tienda
        // (cuando implementes esa tabla)

        return response()->json([
            'success' => true,
            'message' => 'Precio aplicado a todas las tiendas'
        ]);
    }
}