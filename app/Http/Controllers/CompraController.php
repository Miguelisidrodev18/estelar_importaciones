<?php
// app/Http/Controllers/CompraController.php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Almacen;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Categoria;
use App\Services\CompraService;
use App\Services\VarianteService;
use App\Models\Catalogo\Color;
use App\Models\Catalogo\Marca;
use App\Services\CodigoBarrasService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    protected $compraService;
    protected $codigoBarrasService;

    public function __construct(CompraService $compraService, CodigoBarrasService $codigoBarrasService)
    {
        $this->compraService = $compraService;
        $this->codigoBarrasService = $codigoBarrasService;
    }

    public function index()
    {
        $compras = Compra::with('proveedor', 'usuario', 'almacen')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('compras.index', compact('compras'));
    }

    public function create()
    {
        $proveedores = Proveedor::where('estado', 'activo')
            ->orderBy('razon_social')
            ->get();
            
        $almacenes = Almacen::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();
            
        $marcas = Marca::where('estado', 'activo')->orderBy('nombre')->get();

        $productos = Producto::with(['categoria', 'marca', 'modelo', 'variantesActivas.color'])
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get()
            ->map(function($producto) {
                $variantes = $producto->variantesActivas->map(fn($v) => [
                    'id'           => $v->id,
                    'color_id'     => $v->color_id,
                    'color_nombre' => $v->color?->nombre,
                    'color_hex'    => $v->color?->codigo_hex,
                    'capacidad'    => $v->capacidad,
                    'stock_actual' => (int)$v->stock_actual,
                ]);
                return [
                    'id'              => $producto->id,
                    'nombre'          => $producto->nombre,
                    'tipo_inventario' => $producto->tipo_inventario,
                    'categoria'       => $producto->categoria->nombre ?? 'N/A',
                    'categoria_id'    => $producto->categoria_id,
                    'marca_id'        => $producto->marca_id,
                    'marca'           => $producto->marca?->nombre,
                    'modelo_id'       => $producto->modelo_id,
                    'modelo'          => $producto->modelo?->nombre,
                    'unidad_medida'   => $producto->unidadMedida?->abreviatura ?? 'UND',
                    'requiere_imei'   => $producto->tipo_inventario === 'serie',
                    'tiene_variantes' => $variantes->isNotEmpty(),
                    'variantes'       => $variantes,
                ];
            });

        $colores     = Color::where('estado', 'activo')->orderBy('nombre')->get();
        $categorias  = Categoria::activas()->orderBy('nombre')->get();

        return view('compras.create', compact('proveedores', 'almacenes', 'productos', 'colores', 'marcas', 'categorias'));
    }

    public function store(Request $request)
    {
        // Validación extendida y completa (PRIMERO)
        $validated = $request->validate([
            // Datos principales
            'proveedor_id' => 'required|exists:proveedores,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'numero_factura' => 'required|string|max:50|unique:compras,numero_factura,NULL,id,proveedor_id,' . $request->proveedor_id,
            'fecha' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha',
            'observaciones' => 'nullable|string',
            
            // Datos financieros
            'forma_pago' => 'required|in:contado,credito',
            'condicion_pago' => 'required_if:forma_pago,credito|nullable|integer|min:1|max:90',
            'tipo_moneda' => 'required|in:PEN,USD',
            'tipo_cambio' => 'required_if:tipo_moneda,USD|nullable|numeric|min:0.001',
            'incluye_igv' => 'boolean',
            'tipo_operacion' => 'required|in:01,02,03,04',
            'descuento_global' => 'nullable|numeric|min:0|max:100',
            'monto_adicional' => 'nullable|numeric|min:0',
            'concepto_adicional' => 'nullable|string|max:255',
            
            // Datos de envío
            'guia_remision' => 'nullable|string|max:50',
            'transportista' => 'nullable|string|max:255',
            'placa_vehiculo' => 'nullable|string|max:10',
            
            // Detalles de productos
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.variante_id' => 'nullable|exists:producto_variantes,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0.01',
            'detalles.*.descuento' => 'nullable|numeric|min:0|max:100',
            'detalles.*.codigo_barras' => 'nullable|string|max:50',
            'detalles.*.modelo_id' => 'nullable|exists:modelos,id',
            'detalles.*.color_id'  => 'nullable|exists:colores,id',
            'detalles.*.capacidad' => 'nullable|string|max:50',
            'detalles.*.imeis' => 'nullable|array',
            'detalles.*.imeis.*.codigo_imei' => 'required_with:detalles.*.imeis|string|size:15|distinct',
            'detalles.*.imeis.*.serie' => 'nullable|string|max:50',
        ], [
            'numero_factura.unique' => 'Ya existe una compra con este número de factura para el proveedor seleccionado',
            'detalles.required' => 'Debe agregar al menos un producto',
            'detalles.*.precio_unitario.min' => 'El precio debe ser mayor a 0',
            'detalles.*.imeis.*.codigo_imei.size' => 'El IMEI debe tener exactamente 15 dígitos',
            'detalles.*.imeis.*.codigo_imei.distinct' => 'No puede haber IMEI duplicados en el mismo producto',
        ]);

        try {
            DB::beginTransaction();

            // Calcular montos
            $subtotal = 0;
            foreach ($validated['detalles'] as $detalle) {
                $precioConDescuento = $detalle['precio_unitario'];
                if (!empty($detalle['descuento'])) {
                    $precioConDescuento = $detalle['precio_unitario'] * (1 - $detalle['descuento'] / 100);
                }
                $subtotal += $detalle['cantidad'] * $precioConDescuento;
            }

            // Aplicar descuento global si existe
            if (!empty($validated['descuento_global'])) {
                $subtotal = $subtotal * (1 - $validated['descuento_global'] / 100);
            }

            // Agregar monto adicional si existe
            if (!empty($validated['monto_adicional'])) {
                $subtotal += $validated['monto_adicional'];
            }

            // Calcular IGV según tipo de operación SUNAT y estado del checkbox
            $incluyeIgv    = filter_var($validated['incluye_igv'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $tipoOperacion = $validated['tipo_operacion'] ?? '01';

            $igv   = 0;
            $total = $subtotal;
            if ($tipoOperacion === '01' && $incluyeIgv) {
                $igv   = round($subtotal * 0.18, 2);
                $total = $subtotal + $igv;
            }

            // Aplicar tipo de cambio si es USD
            if ($validated['tipo_moneda'] === 'USD' && !empty($validated['tipo_cambio'])) {
                $totalPEN = $total * $validated['tipo_cambio'];
            } else {
                $totalPEN = $total;
            }

            // Preparar datos para el servicio
            $datosCompra = [
                'proveedor_id' => $validated['proveedor_id'],
                'user_id' => auth()->id(),
                'almacen_id' => $validated['almacen_id'],
                'numero_factura' => $validated['numero_factura'],
                'fecha' => $validated['fecha'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
                'forma_pago' => $validated['forma_pago'],
                'condicion_pago' => $validated['forma_pago'] === 'credito' 
                    ? (int)($validated['condicion_pago'] ?? 0) 
                    : null,
                'tipo_moneda' => $validated['tipo_moneda'],
                'tipo_cambio' => $validated['tipo_cambio'] ?? 1,
                'incluye_igv' => $incluyeIgv,
                'subtotal' => $subtotal,
                'igv' => $igv,
                'tipo_operacion' => $validated['tipo_operacion'],
                'total' => $total,
                'total_pen' => $totalPEN,
                'descuento_global' => $validated['descuento_global'] ?? 0,
                'monto_adicional' => $validated['monto_adicional'] ?? 0,
                'concepto_adicional' => $validated['concepto_adicional'] ?? null,
                'guia_remision' => $validated['guia_remision'] ?? null,
                'transportista' => $validated['transportista'] ?? null,
                'placa_vehiculo' => $validated['placa_vehiculo'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
            ];

            // Registrar la compra usando el servicio
            $compra = $this->compraService->registrarCompra($datosCompra, $validated['detalles']);

            DB::commit();

            return redirect()
                ->route('compras.show', $compra)
                ->with('success', 'Compra registrada exitosamente. N° Factura: ' . $compra->numero_factura);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error al registrar la compra: ' . $e->getMessage());
        }
    }

    /**
     * Obtener tipo de cambio actual desde SUNAT (vía api.apis.net.pe)
     */
    public function tipoCambio()
    {
        try {
            $fecha = now()->format('Y-m-d');
            $response = \Illuminate\Support\Facades\Http::timeout(6)
                ->withHeaders(['Accept' => 'application/json'])
                ->get('https://api.apis.net.pe/v1/tipo-cambio-sunat', ['fecha' => $fecha]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'compra'  => $data['compra']  ?? null,
                    'venta'   => $data['venta']   ?? null,
                    'fecha'   => $data['fecha']   ?? $fecha,
                ]);
            }
        } catch (\Exception $e) {
            // silenciar y devolver error amigable
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo conectar al servicio de tipo de cambio. Ingresa el valor manualmente.',
        ]);
    }

    public function show(Compra $compra)
    {
        $compra->load(['proveedor', 'usuario', 'almacen', 'detalles.producto']);

        return view('compras.show', compact('compra'));
    }

    public function edit(Compra $compra)
    {
        // Solo permitir editar si está en estado 'pendiente' o 'borrador'
        if (!in_array($compra->estado, ['pendiente', 'borrador'])) {
            return redirect()
                ->route('compras.show', $compra)
                ->with('error', 'No se puede editar una compra procesada o anulada');
        }

        // Cargar relaciones necesarias
        $compra->load('detalles.producto');
        
        $proveedores = Proveedor::where('estado', 'activo')
            ->orderBy('razon_social') // 🔴 Cambiado de 'nombre' a 'razon_social'
            ->get();
            
        $almacenes = Almacen::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();
        
        return view('compras.edit', compact('compra', 'proveedores', 'almacenes'));
    }

    public function update(Request $request, Compra $compra)
    {
        // Solo permitir editar compras pendientes o borrador
        if (!in_array($compra->estado, ['pendiente', 'borrador'])) {
            return back()->with('error', 'No se puede editar una compra procesada o anulada');
        }

        $validated = $request->validate([
            'almacen_id' => 'required|exists:almacenes,id',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $compra->update([
                'almacen_id' => $validated['almacen_id'],
                'fecha' => $validated['fecha'],
                'observaciones' => $validated['observaciones'],
            ]);

            return redirect()->route('compras.show', $compra)
                ->with('success', 'Compra actualizada correctamente');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function destroy(Compra $compra)
    {
        try {
            DB::beginTransaction();
            
            // Verificar si se puede eliminar
            if ($compra->estado !== 'pendiente') {
                throw new \Exception('No se puede eliminar una compra procesada');
            }
            
            $this->compraService->eliminarCompra($compra);
            
            DB::commit();
            
            return redirect()
                ->route('compras.index')
                ->with('success', 'Compra eliminada exitosamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }


    /**
 * Mostrar vista de importación masiva de IMEI
 */
public function importarIMEI(Request $request)
{
    $productoId = $request->get('producto_id');
    $cantidad = $request->get('cantidad');
    $producto = Producto::findOrFail($productoId);
    
    return view('compras.importar-imei', compact('producto', 'cantidad'));
}

    /**
     * Procesar archivo de IMEI
     */
    public function procesarImportacionIMEI(Request $request, CompraService $compraService)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:2048',
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'index' => 'required|integer', // índice del producto en la compra
            'color_id' => 'required|exists:colores,id',
        ]);
        
        try {
            $resultado = $compraService->procesarArchivoIMEI(
                $request->file('archivo'),
                $request->producto_id,
                $request->cantidad
            );
            
            if ($resultado['success']) {
                // Devolver los IMEI procesados para agregarlos al formulario
                return response()->json([
                    'success' => true,
                    'imeis' => $resultado['imeis'],
                    'index' => $request->index,
                    'color_id' => $request->color_id,
                    'message' => 'IMEI procesados correctamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'errores' => $resultado['errores']
                ], 422);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function imprimir(Compra $compra)
    {
        // Generar PDF de la compra
        $pdf = \PDF::loadView('compras.pdf', compact('compra'));
        return $pdf->download('compra-' . $compra->numero_factura . '.pdf');
    }

    public function anular(Compra $compra)
    {
        try {
            DB::beginTransaction();
            
            if ($compra->estado === 'anulado') {
                throw new \Exception('La compra ya está anulada');
            }
            
            $this->compraService->anularCompra($compra);
            
            DB::commit();
            
            return redirect()
                ->route('compras.show', $compra)
                ->with('success', 'Compra anulada exitosamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al anular: ' . $e->getMessage());
        }
    }

    public function getProductosPorProveedor($proveedorId)
    {
        // Obtener productos que suele vender este proveedor
        $productos = Producto::whereHas('compras', function($query) use ($proveedorId) {
                $query->where('proveedor_id', $proveedorId);
            })
            ->orWhere('estado', 'activo')
            ->orderBy('nombre')
            ->get();
            
        return response()->json($productos);
    }

    public function verificarFactura(Request $request)
    {
        // Verificar si el número de factura ya existe para el proveedor
        $existe = Compra::where('proveedor_id', $request->proveedor_id)
            ->where('numero_factura', $request->numero_factura)
            ->exists();
            
        return response()->json(['existe' => $existe]);
    }
    // Agrega estos métodos al final de tu CompraController.php

    /**
     * Buscar productos para el modal de selección (AJAX)
     * Incluye variantes agrupadas por producto base.
     */
    public function buscarProductos(Request $request)
    {
        $termino = $request->get('q', '');

        $query = Producto::with(['marca', 'modelo', 'categoria', 'variantesActivas.color'])
            ->where('estado', 'activo')
            ->where(function($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                  ->orWhere('codigo', 'like', "%{$termino}%")
                  ->orWhereHas('marca',  fn($m) => $m->where('nombre', 'like', "%{$termino}%"))
                  ->orWhereHas('modelo', fn($m) => $m->where('nombre', 'like', "%{$termino}%"))
                  ->orWhereHas('variantesActivas', fn($v) => $v->where('sku', 'like', "%{$termino}%"));
            })
            ->limit(20)
            ->get()
            ->map(function($producto) {
                $variantes = $producto->variantesActivas->map(fn($v) => [
                    'id'              => $v->id,
                    'sku'             => $v->sku,
                    'color_id'        => $v->color_id,
                    'color_nombre'    => $v->color?->nombre,
                    'color_hex'       => $v->color?->codigo_hex,
                    'capacidad'       => $v->capacidad,
                    'sobreprecio'     => (float)$v->sobreprecio,
                    'stock_actual'    => (int)$v->stock_actual,
                    'nombre_completo' => $v->nombre_completo,
                ]);

                return [
                    'id'              => $producto->id,
                    'nombre'          => $producto->nombre,
                    'codigo'          => $producto->codigo,
                    'marca'           => $producto->marca?->nombre,
                    'modelo'          => $producto->modelo?->nombre,
                    'categoria'       => $producto->categoria?->nombre,
                    'categoria_id'    => $producto->categoria_id,
                    'tipo_inventario' => $producto->tipo_inventario,
                    'marca_id'        => $producto->marca_id,
                    'modelo_id'       => $producto->modelo_id,
                    'imagen'          => $producto->imagen_url ?? null,
                    'tiene_variantes' => $variantes->isNotEmpty(),
                    'variantes'       => $variantes,
                ];
            });

        return response()->json($query);
    }

    /**
     * Crear un producto rápido desde el formulario de compra (AJAX)
     */
    public function crearProductoRapido(Request $request)
    {
        $validated = $request->validate([
            'nombre'          => 'required|string|max:255',
            'categoria_id'    => 'required|exists:categorias,id',
            'marca_id'        => 'required|exists:marcas,id',
            'modelo_id'       => 'nullable|exists:modelos,id',
            'color_id'        => 'nullable|exists:colores,id',
            'tipo_inventario' => 'required|in:serie,regular',
            'tiene_variantes' => 'boolean',
            'codigo_barras'   => 'nullable|string|max:100',
        ], [
            'nombre.required'       => 'El nombre del producto es obligatorio.',
            'categoria_id.required' => 'Selecciona una categoría.',
            'marca_id.required'     => 'Selecciona una marca.',
        ]);

        try {
            $producto = Producto::create([
                'codigo'          => Producto::generarCodigo(),
                'nombre'          => $validated['nombre'],
                'categoria_id'    => $validated['categoria_id'],
                'marca_id'        => $validated['marca_id'],
                'modelo_id'       => $validated['modelo_id'] ?? null,
                'color_id'        => $validated['color_id'] ?? null,
                'tipo_inventario' => $validated['tipo_inventario'],
                'codigo_barras'   => $validated['codigo_barras'] ?? null,
                'estado'          => 'activo',
                'stock_actual'    => 0,
                'stock_minimo'    => 0,
                'stock_maximo'    => 0,
                'creado_por'      => auth()->id(),
            ]);

            $producto->load('categoria', 'marca', 'modelo');

            return response()->json([
                'success'         => true,
                'id'              => $producto->id,
                'nombre'          => $producto->nombre,
                'tipo_inventario' => $producto->tipo_inventario,
                'categoria'       => $producto->categoria?->nombre,
                'marca'           => $producto->marca?->nombre,
                'marca_id'        => $producto->marca_id,
                'modelo'          => $producto->modelo?->nombre,
                'modelo_id'       => $producto->modelo_id,
                'color_id'        => $producto->color_id,
                'codigo_barras'   => $producto->codigo_barras,
                'requiere_imei'   => $producto->tipo_inventario === 'serie',
                'tiene_variantes' => false,   // recién creado, sin variantes aún
                'variantes'       => [],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el producto: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
 * Obtener detalles completos de un producto (para selección rápida)
 */
public function getProductoDetalle($id)
{
    try {
        $producto = Producto::with(['marca', 'modelo', 'categoria', 'variantesActivas.color'])
            ->findOrFail($id);

        $variantes = $producto->variantesActivas->map(fn($v) => [
            'id'              => $v->id,
            'sku'             => $v->sku,
            'color_id'        => $v->color_id,
            'color_nombre'    => $v->color?->nombre,
            'color_hex'       => $v->color?->codigo_hex,
            'capacidad'       => $v->capacidad,
            'sobreprecio'     => (float)$v->sobreprecio,
            'stock_actual'    => (int)$v->stock_actual,
            'nombre_completo' => $v->nombre_completo,
            'tiene_stock'     => $v->tieneStock(),
        ]);

        return response()->json([
            'success'         => true,
            'id'              => $producto->id,
            'nombre'          => $producto->nombre,
            'codigo'          => $producto->codigo,
            'tipo_inventario' => $producto->tipo_inventario,
            'marca_id'        => $producto->marca_id,
            'marca_nombre'    => $producto->marca?->nombre,
            'modelo_id'       => $producto->modelo_id,
            'modelo_nombre'   => $producto->modelo?->nombre,
            'categoria_id'    => $producto->categoria_id,
            'categoria_nombre'=> $producto->categoria?->nombre,
            'tiene_variantes' => $variantes->isNotEmpty(),
            'variantes'       => $variantes,
            'precio_compra'   => (float)($producto->ultimo_costo_compra ?? 0),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error'   => true,
            'message' => 'Error al cargar el producto: ' . $e->getMessage(),
        ], 500);
    }
}
}