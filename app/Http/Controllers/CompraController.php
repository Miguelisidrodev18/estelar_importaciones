<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\Almacen;
use App\Services\CompraService;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    public function index()
    {
        $compras = Compra::with('proveedor', 'almacen', 'usuario')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('compras.index', compact('compras'));
    }

    public function create()
    {
        $proveedores = Proveedor::activos()->orderBy('razon_social')->get();
        $productos = Producto::where('estado', 'activo')->orderBy('nombre')->get();
        $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();

        return view('compras.create', compact('proveedores', 'productos', 'almacenes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'numero_factura' => 'required|string|max:50',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0.01',
            'detalles.*.imeis' => 'nullable|array',
            'detalles.*.imeis.*.codigo_imei' => 'required_with:detalles.*.imeis|string|max:20|distinct',
            'detalles.*.imeis.*.serie' => 'nullable|string|max:50',
            'detalles.*.imeis.*.color' => 'nullable|string|max:50',
        ], [
            'numero_factura.required' => 'El número de factura es obligatorio',
            'detalles.required' => 'Debe agregar al menos un producto',
            'detalles.*.precio_unitario.min' => 'El precio debe ser mayor a 0',
        ]);

        $subtotal = collect($validated['detalles'])->sum(function ($d) {
            return $d['cantidad'] * $d['precio_unitario'];
        });
        $igv = $subtotal * 0.18;
        $total = $subtotal + $igv;

        try {
            $compra = app(CompraService::class)->registrarCompra(
                [
                    'proveedor_id' => $validated['proveedor_id'],
                    'user_id' => auth()->id(),
                    'almacen_id' => $validated['almacen_id'],
                    'numero_factura' => $validated['numero_factura'],
                    'fecha' => $validated['fecha'],
                    'subtotal' => $subtotal,
                    'igv' => $igv,
                    'total' => $total,
                    'observaciones' => $validated['observaciones'],
                ],
                $validated['detalles']
            );

            return redirect()
                ->route('compras.show', $compra)
                ->with('success', 'Compra registrada exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al registrar compra: ' . $e->getMessage());
        }
    }

    public function show(Compra $compra)
    {
        $compra->load('proveedor', 'almacen', 'usuario', 'detalles.producto');

        return view('compras.show', compact('compra'));
    }
}
