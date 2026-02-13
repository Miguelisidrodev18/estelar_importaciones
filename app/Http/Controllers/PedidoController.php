<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Proveedor;
use App\Models\Producto;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with('proveedor', 'usuario')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pedidos.index', compact('pedidos'));
    }

    public function create()
    {
        $proveedores = Proveedor::activos()->orderBy('razon_social')->get();
        $productos = Producto::where('estado', 'activo')->orderBy('nombre')->get();

        return view('pedidos.create', compact('proveedores', 'productos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => 'required|date',
            'fecha_esperada' => 'nullable|date|after_or_equal:fecha',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_referencial' => 'nullable|numeric|min:0',
        ]);

        $pedido = Pedido::create([
            'proveedor_id' => $validated['proveedor_id'],
            'user_id' => auth()->id(),
            'fecha' => $validated['fecha'],
            'fecha_esperada' => $validated['fecha_esperada'],
            'observaciones' => $validated['observaciones'],
            'estado' => 'pendiente',
        ]);

        foreach ($validated['detalles'] as $detalle) {
            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $detalle['producto_id'],
                'cantidad' => $detalle['cantidad'],
                'precio_referencial' => $detalle['precio_referencial'] ?? null,
            ]);
        }

        return redirect()
            ->route('pedidos.index')
            ->with('success', 'Pedido creado exitosamente');
    }

    public function show(Pedido $pedido)
    {
        $pedido->load('proveedor', 'usuario', 'detalles.producto');

        return view('pedidos.show', compact('pedido'));
    }

    public function cambiarEstado(Request $request, Pedido $pedido)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,aprobado,recibido,cancelado',
        ]);

        $pedido->update(['estado' => $validated['estado']]);

        return redirect()
            ->back()
            ->with('success', 'Estado del pedido actualizado');
    }

    public function pedidosProveedor()
    {
        $user = auth()->user();

        $proveedor = Proveedor::where('email', $user->email)
            ->orWhere('ruc', $user->dni)
            ->first();

        $pedidos = collect();

        if ($proveedor) {
            $pedidos = Pedido::with('usuario', 'detalles.producto')
                ->where('proveedor_id', $proveedor->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('pedidos.proveedor', compact('pedidos'));
    }
}
