<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Almacen;
use App\Models\StockAlmacen;
use App\Models\Traslado;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TiendaController extends Controller
{
    /**
     * Constructor - Solo rol Tienda puede acceder
     */
    public function __construct()
    {
        $this->middleware('role:Tienda');
    }

    /**
     * Ver inventario de todas las tiendas/almacenes
     */
    public function inventario(Request $request)
    {
        $tiendaActual = auth()->user()->almacen_id;
        
        $query = Producto::with(['categoria', 'marca', 'modelo']);

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
        
        // Obtener stock por almacén para cada producto
        foreach ($productos as $producto) {
            $producto->stocks = StockAlmacen::where('producto_id', $producto->id)
                ->with('almacen')
                ->get()
                ->keyBy('almacen_id');
        }

        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre')->get();
        $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();
        $tiendaActual = Almacen::find($tiendaActual);

        return view('tienda.inventario', compact('productos', 'categorias', 'almacenes', 'tiendaActual'));
    }

    /**
     * Ver solicitudes de traslado de la tienda actual
     */
    public function solicitudes(Request $request)
    {
        $tiendaActual = auth()->user()->almacen_id;

        $query = Traslado::with(['producto', 'almacenOrigen', 'almacenDestino', 'solicitante'])
            ->where('almacen_destino_id', $tiendaActual)
            ->orderBy('created_at', 'desc');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $solicitudes = $query->paginate(20);

        return view('tienda.solicitudes', compact('solicitudes'));
    }

    /**
     * Crear una solicitud de traslado
     */
    public function solicitarTraslado(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'almacen_origen_id' => 'required|exists:almacenes,id',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Verificar que el almacén origen sea diferente al destino
            if ($validated['almacen_origen_id'] == auth()->user()->almacen_id) {
                throw new \Exception('No puedes solicitar traslado desde tu propio almacén');
            }

            // Verificar stock en origen
            $stockOrigen = StockAlmacen::where([
                'producto_id' => $validated['producto_id'],
                'almacen_id' => $validated['almacen_origen_id']
            ])->first();

            if (!$stockOrigen || $stockOrigen->cantidad < $validated['cantidad']) {
                throw new \Exception('Stock insuficiente en el almacén de origen');
            }

            // Crear solicitud
            $traslado = Traslado::create([
                'codigo' => 'TR-' . date('Ymd') . '-' . rand(1000, 9999),
                'producto_id' => $validated['producto_id'],
                'almacen_origen_id' => $validated['almacen_origen_id'],
                'almacen_destino_id' => auth()->user()->almacen_id,
                'cantidad' => $validated['cantidad'],
                'motivo' => $validated['motivo'] ?? null,
                'solicitado_por' => auth()->id(),
                'estado' => 'pendiente',
                'fecha_solicitud' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de traslado creada correctamente',
                'traslado' => $traslado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar una solicitud de traslado (solo si está pendiente)
     */
    public function cancelarSolicitud(Traslado $traslado)
    {
        try {
            if ($traslado->almacen_destino_id != auth()->user()->almacen_id) {
                throw new \Exception('No tienes permiso para cancelar esta solicitud');
            }

            if ($traslado->estado != 'pendiente') {
                throw new \Exception('Solo se pueden cancelar solicitudes pendientes');
            }

            $traslado->update([
                'estado' => 'cancelado',
                'fecha_cancelacion' => now(),
                'cancelado_por' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud cancelada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de un producto (stock en todas las tiendas)
     */
    public function verProducto(Producto $producto)
    {
        $producto->load(['categoria', 'marca', 'modelo']);
        
        $stocks = StockAlmacen::where('producto_id', $producto->id)
            ->with('almacen')
            ->get();

        return view('tienda.producto', compact('producto', 'stocks'));
    }

    /**
     * Obtener stock de un producto en tiempo real (AJAX)
     */
    public function getStockProducto(Request $request)
    {
        $productoId = $request->get('producto_id');
        $almacenId = $request->get('almacen_id');

        $stock = StockAlmacen::where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->first();

        return response()->json([
            'success' => true,
            'stock' => $stock ? $stock->cantidad : 0
        ]);
    }
}