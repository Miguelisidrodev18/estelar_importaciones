<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Almacen;
use App\Services\TrasladoService;
use Illuminate\Http\Request;

class TrasladoController extends Controller
{
    public function index()
    {
        $traslados = MovimientoInventario::with('producto', 'almacen', 'almacenDestino', 'usuario')
            ->where('tipo_movimiento', 'transferencia')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('traslados.index', compact('traslados'));
    }

    public function create()
    {
        $productos = Producto::where('estado', 'activo')->orderBy('nombre')->get();
        $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();

        return view('traslados.create', compact('productos', 'almacenes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'almacen_destino_id' => 'required|exists:almacenes,id|different:almacen_id',
            'cantidad' => 'required|integer|min:1',
            'imei_id' => 'nullable|exists:imeis,id',
            'transportista' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ], [
            'almacen_destino_id.different' => 'El almacén destino debe ser diferente al origen',
        ]);

        try {
            $movimiento = app(TrasladoService::class)->crearTraslado(
                array_merge($validated, ['user_id' => auth()->id()])
            );

            return redirect()
                ->route('traslados.index')
                ->with('success', "Traslado creado. Guía: {$movimiento->numero_guia}");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(MovimientoInventario $traslado)
    {
        $traslado->load('producto', 'almacen', 'almacenDestino', 'usuario', 'usuarioConfirma', 'imei');

        return view('traslados.show', compact('traslado'));
    }

    public function pendientes()
    {
        $traslados = MovimientoInventario::with('producto', 'almacen', 'almacenDestino', 'usuario')
            ->where('tipo_movimiento', 'transferencia')
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('traslados.pendientes', compact('traslados'));
    }

    public function confirmar(MovimientoInventario $traslado)
    {
        try {
            app(TrasladoService::class)->confirmarRecepcion(
                $traslado->id,
                auth()->id()
            );

            return redirect()
                ->route('traslados.pendientes')
                ->with('success', 'Traslado confirmado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
