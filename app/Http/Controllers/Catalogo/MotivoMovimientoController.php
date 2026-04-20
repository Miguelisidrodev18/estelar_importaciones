<?php
// app/Http/Controllers/Catalogo/MotivoMovimientoController.php

namespace App\Http\Controllers\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\MotivoMovimiento;
use Illuminate\Http\Request;

class MotivoMovimientoController extends Controller
{
    public function index(Request $request)
    {
        $query = MotivoMovimiento::orderBy('nombre');

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $motivos = $query->paginate(15)->withQueryString();
        return view('catalogo.motivos.index', compact('motivos'));
    }

    public function create()
    {
        return view('catalogo.motivos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:motivos_movimiento',
            'codigo' => 'nullable|string|max:50|unique:motivos_movimiento',
            'tipo' => 'required|in:ingreso,salida,transferencia,ajuste,otros',
            'descripcion' => 'nullable|string',
            'requiere_aprobacion' => 'boolean',
            'afecta_stock' => 'boolean',
            'estado' => 'required|in:activo,inactivo'
        ]);

        MotivoMovimiento::create($validated);

        return redirect()
            ->route('catalogo.motivos.index')
            ->with('success', 'Motivo de movimiento creado exitosamente');
    }

    public function edit(MotivoMovimiento $motivo)
    {
        return view('catalogo.motivos.edit', compact('motivo'));
    }

    public function update(Request $request, MotivoMovimiento $motivo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:motivos_movimiento,nombre,' . $motivo->id,
            'codigo' => 'nullable|string|max:50|unique:motivos_movimiento,codigo,' . $motivo->id,
            'tipo' => 'required|in:ingreso,salida,transferencia,ajuste,otros',
            'descripcion' => 'nullable|string',
            'requiere_aprobacion' => 'boolean',
            'afecta_stock' => 'boolean',
            'estado' => 'required|in:activo,inactivo'
        ]);

        $motivo->update($validated);

        return redirect()
            ->route('catalogo.motivos.index')
            ->with('success', 'Motivo de movimiento actualizado exitosamente');
    }

    public function destroy(MotivoMovimiento $motivo)
    {
        // Verificar si está siendo usado
        // if ($motivo->movimientos()->exists()) {
        //     return back()->with('error', 'No se puede eliminar porque tiene movimientos asociados');
        // }

        $motivo->delete();

        return redirect()
            ->route('catalogo.motivos.index')
            ->with('success', 'Motivo de movimiento eliminado exitosamente');
    }
}