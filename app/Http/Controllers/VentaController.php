<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Almacen;
use App\Models\Imei;
use App\Services\VentaService;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Venta::with('vendedor', 'cliente', 'almacen');

        if ($user->role->nombre === 'Vendedor') {
            $query->where('user_id', $user->id);
        }

        $ventas = $query->orderBy('created_at', 'desc')->get();

        return view('ventas.index', compact('ventas'));
    }

    public function create()
    {
        $clientes = Cliente::activos()->orderBy('nombre')->get();
        $productos = Producto::where('estado', 'activo')->orderBy('nombre')->get();
        $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();

        return view('ventas.create', compact('clientes', 'productos', 'almacenes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0.01',
            'detalles.*.imei_id' => 'nullable|exists:imeis,id',
        ], [
            'detalles.required' => 'Debe agregar al menos un producto',
        ]);

        $subtotal = collect($validated['detalles'])->sum(function ($d) {
            return $d['cantidad'] * $d['precio_unitario'];
        });

        try {
            $venta = app(VentaService::class)->crearVenta(
                [
                    'user_id' => auth()->id(),
                    'cliente_id' => $validated['cliente_id'],
                    'almacen_id' => $validated['almacen_id'],
                    'fecha' => now()->toDateString(),
                    'subtotal' => $subtotal,
                    'igv' => 0,
                    'total' => $subtotal,
                    'observaciones' => $validated['observaciones'],
                ],
                $validated['detalles']
            );

            return redirect()
                ->route('ventas.show', $venta)
                ->with('success', 'Venta creada. Pendiente de confirmación de pago.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(Venta $venta)
    {
        $venta->load('vendedor', 'confirmador', 'cliente', 'almacen', 'detalles.producto', 'detalles.imei');

        return view('ventas.show', compact('venta'));
    }

    public function confirmarPago(Request $request, Venta $venta)
    {
        $validated = $request->validate([
            'metodo_pago' => 'required|in:efectivo,transferencia,yape,plin',
        ]);

        try {
            app(VentaService::class)->confirmarPago(
                $venta->id,
                $validated['metodo_pago'],
                auth()->id()
            );

            return redirect()
                ->route('ventas.show', $venta)
                ->with('success', 'Pago confirmado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function imeisDisponibles(Request $request)
    {
        $productoId = $request->input('producto_id');
        $almacenId = $request->input('almacen_id');

        $imeis = Imei::where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->where('estado', 'disponible')
            ->get(['id', 'codigo_imei', 'serie', 'color']);

        return response()->json($imeis);
    }
}
