<?php
// app/Http/Controllers/CompraController.php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Almacen;
use App\Models\Producto;
use App\Services\CompraService;
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

        $productos = Producto::with('categoria', 'marca', 'modelo')
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get()
            ->map(function($producto) {
                return [
                    'id'            => $producto->id,
                    'nombre'        => $producto->nombre,
                    'tipo_producto' => $producto->tipo_producto,
                    'categoria'     => $producto->categoria->nombre ?? 'N/A',
                    'marca_id'      => $producto->marca_id,
                    'marca'         => $producto->marca?->nombre,
                    'modelo_id'     => $producto->modelo_id,
                    'modelo'        => $producto->modelo?->nombre,
                    'unidad_medida' => $producto->unidad_medida ?? 'UND',
                    'requiere_imei' => $producto->tipo_producto === 'celular',
                ];
            });

        $colores = Color::where('estado', 'activo')->orderBy('nombre')->get();

        return view('compras.create', compact('proveedores', 'almacenes', 'productos', 'colores', 'marcas'));
    }

    public function store(Request $request)
    {
        // Validación extendida y completa
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
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0.01',
            'detalles.*.descuento' => 'nullable|numeric|min:0|max:100',
            'detalles.*.codigo_barras' => 'nullable|string|max:50',
            'detalles.*.modelo_id' => 'nullable|exists:modelos,id',
            'detalles.*.color_id'  => 'nullable|exists:colores,id',
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

            // Calcular IGV según corresponda
            $incluyeIgv = $validated['incluye_igv'] ?? true;
            $igv = $incluyeIgv ? $subtotal * 0.18 : 0;
            $total = $incluyeIgv ? $subtotal : $subtotal + $igv;

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
                'condicion_pago' => $validated['condicion_pago'] ?? null,
                'tipo_moneda' => $validated['tipo_moneda'],
                'tipo_cambio' => $validated['tipo_cambio'] ?? 1,
                'incluye_igv' => $incluyeIgv,
                'subtotal' => $subtotal,
                'igv' => $igv,
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

    public function show(Compra $compra)
    {
        $compra->load(['proveedor', 'usuario', 'almacen', 'detalles.producto']);
        
        return view('compras.show', compact('compra'));
    }

    public function edit(Compra $compra)
    {
        // Solo permitir editar si está en estado 'pendiente' o similar
        if (!in_array($compra->estado, ['pendiente', 'borrador'])) {
            return redirect()
                ->route('compras.show', $compra)
                ->with('error', 'No se puede editar una compra procesada');
        }

        $proveedores = Proveedor::where('estado', 'activo')->orderBy('nombre')->get();
        $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();
        $productos = Producto::where('estado', 'activo')->orderBy('nombre')->get();
        
        return view('compras.edit', compact('compra', 'proveedores', 'almacenes', 'productos'));
    }

    public function update(Request $request, Compra $compra)
    {
        // Similar a store pero para actualización
        // Validar que la compra sea editable
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
}