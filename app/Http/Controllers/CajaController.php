<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Almacen;
use App\Services\CajaService;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function index()
    {
        $cajas = Caja::with('usuario', 'almacen')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('caja.index', compact('cajas'));
    }

    public function abrir()
    {
        $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();
        $cajaAbierta = Caja::where('user_id', auth()->id())
            ->where('estado', 'abierta')
            ->first();

        return view('caja.abrir', compact('almacenes', 'cajaAbierta'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'almacen_id' => 'required|exists:almacenes,id',
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        try {
            app(CajaService::class)->abrirCaja(
                auth()->id(),
                $validated['almacen_id'],
                $validated['monto_inicial']
            );

            return redirect()
                ->route('caja.actual')
                ->with('success', 'Caja abierta exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function actual()
    {
        $caja = Caja::with('movimientos', 'almacen')
            ->where('user_id', auth()->id())
            ->where('estado', 'abierta')
            ->first();

        if (!$caja) {
            return redirect()
                ->route('caja.abrir')
                ->with('error', 'No tienes una caja abierta');
        }

        return view('caja.actual', compact('caja'));
    }

    public function cerrar(Request $request)
    {
        $validated = $request->validate([
            'caja_id' => 'required|exists:caja,id',
            'monto_final_real' => 'required|numeric|min:0',
        ]);

        try {
            $caja = app(CajaService::class)->cerrarCaja(
                $validated['caja_id'],
                $validated['monto_final_real']
            );

            return redirect()
                ->route('caja.index')
                ->with('success', 'Caja cerrada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
