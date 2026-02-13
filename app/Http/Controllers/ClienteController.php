<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Services\SunatService;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::withCount('ventas')
            ->orderBy('nombre')
            ->get();

        $canCreate = in_array(auth()->user()->role->nombre, ['Administrador', 'Vendedor', 'Tienda']);
        $canEdit = in_array(auth()->user()->role->nombre, ['Administrador', 'Vendedor', 'Tienda']);
        $canDelete = auth()->user()->role->nombre === 'Administrador';

        return view('clientes.index', compact('clientes', 'canCreate', 'canEdit', 'canDelete'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_documento' => 'required|in:DNI,RUC,CE',
            'numero_documento' => 'required|string|max:11|unique:clientes,numero_documento',
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'estado' => 'required|in:activo,inactivo',
        ], [
            'numero_documento.unique' => 'Este documento ya está registrado',
            'nombre.required' => 'El nombre es obligatorio',
        ]);

        Cliente::create($validated);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente registrado exitosamente');
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'tipo_documento' => 'required|in:DNI,RUC,CE',
            'numero_documento' => 'required|string|max:11|unique:clientes,numero_documento,' . $cliente->id,
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'estado' => 'required|in:activo,inactivo',
        ]);

        $cliente->update($validated);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy(Cliente $cliente)
    {
        try {
            $cliente->delete();
            return redirect()
                ->route('clientes.index')
                ->with('success', 'Cliente eliminado exitosamente');
        } catch (\Exception $e) {
            return redirect()
                ->route('clientes.index')
                ->with('error', 'No se puede eliminar el cliente porque tiene ventas asociadas');
        }
    }

    public function consultarDocumento(Request $request)
    {
        $tipo = $request->input('tipo');
        $numero = $request->input('numero');
        $sunat = app(SunatService::class);

        if ($tipo === 'RUC') {
            return response()->json($sunat->consultarRuc($numero));
        }

        return response()->json($sunat->consultarDni($numero));
    }
}
