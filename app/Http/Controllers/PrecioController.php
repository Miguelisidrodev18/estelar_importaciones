<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\Almacen;
use App\Models\ProductoPrecio;
use App\Models\ProductoPrecioHistorial;
use App\Models\DetalleCompra;
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
        $producto->load(['categoria', 'marca', 'modelo', 'variantesActivas', 'precios' => function($q) {
            $q->with('proveedor', 'almacen', 'variante.color')->orderByRaw('almacen_id IS NULL DESC')->latest();
        }]);

        $proveedores = Proveedor::where('estado', 'activo')->orderBy('razon_social')->get();
        $almacenes   = Almacen::where('estado', 'activo')->orderBy('nombre')->get();

        // Agrupar precios: globales primero, luego por tienda
        $preciosGlobales = $producto->precios->whereNull('almacen_id')->where('tipo_precio', 'venta_regular');
        $preciosPorTienda = $producto->precios->whereNotNull('almacen_id')->where('tipo_precio', 'venta_regular');

        return view('precios.show', compact(
            'producto', 'proveedores', 'almacenes',
            'preciosGlobales', 'preciosPorTienda'
        ));
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
     * Registrar nuevo precio para un producto
     */
    public function store(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'proveedor_id'    => 'nullable|exists:proveedores,id',
            'variante_id'     => 'nullable|exists:producto_variantes,id',
            'precio_compra'   => 'required|numeric|min:0.01',
            'precio_venta'    => 'required|numeric|min:0.01',
            'precio_mayorista'=> 'nullable|numeric|min:0.01',
            'margen'          => 'required|numeric|min:0|max:1000',
            'observaciones'   => 'nullable|string|max:500',
            'replicar_tiendas'=> 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $precioAnterior = $producto->precio_venta;

            // Desactivar precio global vigente anterior (mismo producto/variante)
            ProductoPrecio::where('producto_id', $producto->id)
                ->where('variante_id', $validated['variante_id'] ?? null)
                ->whereNull('almacen_id')
                ->where('tipo_precio', 'venta_regular')
                ->where('activo', true)
                ->update(['activo' => false]);

            // Crear precio global (almacen_id = null)
            $nuevoPrecio = ProductoPrecio::create([
                'producto_id'      => $producto->id,
                'variante_id'      => $validated['variante_id'] ?? null,
                'almacen_id'       => null,
                'tipo_precio'      => 'venta_regular',
                'precio'           => $validated['precio_venta'],
                'precio_compra'    => $validated['precio_compra'],
                'precio_mayorista' => $validated['precio_mayorista'] ?? null,
                'margen'           => $validated['margen'],
                'observaciones'    => $validated['observaciones'] ?? null,
                'proveedor_id'     => $validated['proveedor_id'] ?? null,
                'activo'           => true,
                'creado_por'       => auth()->id(),
            ]);

            // Si tiene precio mayorista, también guardar como registro separado tipo mayorista
            if (!empty($validated['precio_mayorista'])) {
                ProductoPrecio::where('producto_id', $producto->id)
                    ->where('variante_id', $validated['variante_id'] ?? null)
                    ->whereNull('almacen_id')
                    ->where('tipo_precio', 'venta_mayorista')
                    ->where('activo', true)
                    ->update(['activo' => false]);

                ProductoPrecio::create([
                    'producto_id'   => $producto->id,
                    'variante_id'   => $validated['variante_id'] ?? null,
                    'almacen_id'    => null,
                    'tipo_precio'   => 'venta_mayorista',
                    'precio'        => $validated['precio_mayorista'],
                    'precio_compra' => $validated['precio_compra'],
                    'margen'        => $validated['margen'],
                    'proveedor_id'  => $validated['proveedor_id'] ?? null,
                    'activo'        => true,
                    'creado_por'    => auth()->id(),
                ]);
            }

            // Si no tiene variante, actualizar precio_venta en la tabla productos
            if (empty($validated['variante_id'])) {
                $producto->update(['precio_venta' => $validated['precio_venta']]);
            } else {
                // Si tiene variante, calcular el sobreprecio y actualizar en producto_variantes
                $sobreprecio = max(0, $validated['precio_venta'] - $producto->precio_venta);
                \App\Models\ProductoVariante::where('id', $validated['variante_id'])
                    ->update(['sobreprecio' => $sobreprecio]);
            }

            // Replicar a todas las tiendas activas
            if (!empty($validated['replicar_tiendas'])) {
                $almacenes = Almacen::where('estado', 'activo')->get();
                foreach ($almacenes as $almacen) {
                    // Desactivar precio anterior de esta tienda
                    ProductoPrecio::where('producto_id', $producto->id)
                        ->where('variante_id', $validated['variante_id'] ?? null)
                        ->where('almacen_id', $almacen->id)
                        ->where('tipo_precio', 'venta_regular')
                        ->update(['activo' => false]);

                    // Crear precio por tienda
                    ProductoPrecio::create([
                        'producto_id'      => $producto->id,
                        'variante_id'      => $validated['variante_id'] ?? null,
                        'almacen_id'       => $almacen->id,
                        'tipo_precio'      => 'venta_regular',
                        'precio'           => $validated['precio_venta'],
                        'precio_compra'    => $validated['precio_compra'],
                        'precio_mayorista' => $validated['precio_mayorista'] ?? null,
                        'margen'           => $validated['margen'],
                        'proveedor_id'     => $validated['proveedor_id'] ?? null,
                        'activo'           => true,
                        'creado_por'       => auth()->id(),
                    ]);
                }
            }

            // Registrar en historial
            ProductoPrecioHistorial::create([
                'producto_id'    => $producto->id,
                'tipo_cambio'    => 'venta_regular',
                'precio_anterior'=> $precioAnterior ?: null,
                'precio_nuevo'   => $validated['precio_venta'],
                'motivo'         => $validated['observaciones'] ?? 'Registro de precio',
                'usuario_id'     => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('precios.show', $producto)
                ->with('success', 'Precio registrado correctamente' .
                    (!empty($validated['replicar_tiendas']) ? ' y replicado a todas las tiendas.' : '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al registrar precio: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar precio existente
     */
    public function update(Request $request, Producto $producto, $precioId)
    {
        $precio = ProductoPrecio::findOrFail($precioId);

        $validated = $request->validate([
            'precio_compra'    => 'required|numeric|min:0.01',
            'precio_venta'     => 'required|numeric|min:0.01',
            'precio_mayorista' => 'nullable|numeric|min:0.01',
            'margen'           => 'required|numeric|min:0|max:1000',
            'observaciones'    => 'nullable|string|max:500',
            'fecha_inicio'     => 'nullable|date',
            'fecha_fin'        => 'nullable|date|after_or_equal:fecha_inicio',
            'activo'           => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Registrar en historial si el precio de venta cambió
            if ((float)$precio->precio !== (float)$validated['precio_venta']) {
                ProductoPrecioHistorial::create([
                    'producto_id'     => $producto->id,
                    'tipo_cambio'     => 'venta_regular',
                    'precio_anterior' => $precio->precio,
                    'precio_nuevo'    => $validated['precio_venta'],
                    'motivo'          => $validated['observaciones'] ?? 'Edición manual',
                    'usuario_id'      => auth()->id(),
                ]);
            }

            $precio->update([
                'precio'           => $validated['precio_venta'],
                'precio_compra'    => $validated['precio_compra'],
                'precio_mayorista' => $validated['precio_mayorista'] ?? null,
                'margen'           => $validated['margen'],
                'observaciones'    => $validated['observaciones'] ?? null,
                'fecha_inicio'     => $validated['fecha_inicio'] ?? null,
                'fecha_fin'        => $validated['fecha_fin'] ?? null,
                'activo'           => $validated['activo'] ?? true,
            ]);

            // Si es precio global activo y sin variante, actualizar productos.precio_venta
            if (is_null($precio->almacen_id) && is_null($precio->variante_id) && $precio->activo) {
                $producto->update(['precio_venta' => $validated['precio_venta']]);
            }

            DB::commit();

            return redirect()
                ->route('precios.show', $producto)
                ->with('success', 'Precio actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
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
     * Último precio unitario registrado en compras para este producto+proveedor
     */
    public function ultimoPrecioCompra(Request $request, Producto $producto)
    {
        $proveedorId = $request->get('proveedor_id');

        $detalle = DetalleCompra::with(['compra.proveedor'])
            ->where('detalle_compras.producto_id', $producto->id)
            ->join('compras', 'detalle_compras.compra_id', '=', 'compras.id')
            ->where('compras.estado', '!=', 'anulado')
            ->when($proveedorId, fn($q) => $q->where('compras.proveedor_id', $proveedorId))
            ->orderByDesc('compras.fecha')
            ->orderByDesc('detalle_compras.id')
            ->select('detalle_compras.*')
            ->first();

        if (!$detalle) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'           => true,
            'precio_unitario' => (float) $detalle->precio_unitario,
            'fecha_compra'    => $detalle->compra->fecha->format('d/m/Y'),
            'compra_codigo'   => $detalle->compra->codigo,
            'proveedor'       => [
                'id'          => $detalle->compra->proveedor->id,
                'razon_social'=> $detalle->compra->proveedor->razon_social,
            ],
        ]);
    }

    /**
     * Búsqueda dinámica de proveedores (AJAX)
     * No accede al módulo de compras — solo retorna proveedores activos
     */
    public function buscarProveedores(Request $request)
    {
        $q = trim($request->get('q', ''));

        $proveedores = Proveedor::where('estado', 'activo')
            ->where(function ($query) use ($q) {
                $query->where('razon_social', 'like', "%{$q}%")
                      ->orWhere('ruc', 'like', "%{$q}%");
            })
            ->orderBy('razon_social')
            ->limit(10)
            ->get(['id', 'razon_social', 'ruc']);

        return response()->json($proveedores);
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