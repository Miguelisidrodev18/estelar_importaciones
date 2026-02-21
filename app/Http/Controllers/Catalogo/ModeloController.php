<?php
// app/Http/Controllers/Catalogo/ModeloController.php

namespace App\Http\Controllers\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\Modelo;
use App\Models\Catalogo\Marca;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ModeloController extends Controller
{
    public function index(Request $request)
    {
        $query = Modelo::with('marca', 'categoria')->orderBy('nombre');

        if ($request->filled('marca_id')) {
            $query->where('marca_id', $request->marca_id);
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $modelos    = $query->paginate(15)->withQueryString();
        $marcas     = Marca::where('estado', 'activo')->orderBy('nombre')->get();
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre')->get();

        return view('catalogo.modelos.index', compact('modelos', 'marcas', 'categorias'));
    }

    public function create()
    {
        $marcas = Marca::where('estado', 'activo')->orderBy('nombre')->get();
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre')->get();
        
        return view('catalogo.modelos.create', compact('marcas', 'categorias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'marca_id' => 'required|exists:marcas,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'codigo_modelo' => 'nullable|string|max:50',
            'especificaciones_tecnicas' => 'nullable|string',
            'imagen_referencia' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'estado' => 'required|in:activo,inactivo'
        ]);

        // Validar unicidad por marca
        $exists = Modelo::where('marca_id', $validated['marca_id'])
            ->where('nombre', $validated['nombre'])
            ->exists();
            
        if ($exists) {
            return back()->withInput()->with('error', 'Ya existe un modelo con ese nombre para esta marca');
        }

        if ($request->hasFile('imagen_referencia')) {
            $path = $request->file('imagen_referencia')->store('modelos', 'public');
            $validated['imagen_referencia'] = $path;
        }

        Modelo::create($validated);

        return redirect()
            ->route('catalogo.modelos.index')
            ->with('success', 'Modelo creado exitosamente');
    }

    public function edit(Modelo $modelo)
    {
        $marcas = Marca::where('estado', 'activo')->orderBy('nombre')->get();
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre')->get();
        
        return view('catalogo.modelos.edit', compact('modelo', 'marcas', 'categorias'));
    }

    public function update(Request $request, Modelo $modelo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'marca_id' => 'required|exists:marcas,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'codigo_modelo' => 'nullable|string|max:50',
            'especificaciones_tecnicas' => 'nullable|string',
            'imagen_referencia' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'estado' => 'required|in:activo,inactivo'
        ]);

        // Validar unicidad por marca (excluyendo el actual)
        $exists = Modelo::where('marca_id', $validated['marca_id'])
            ->where('nombre', $validated['nombre'])
            ->where('id', '!=', $modelo->id)
            ->exists();
            
        if ($exists) {
            return back()->withInput()->with('error', 'Ya existe un modelo con ese nombre para esta marca');
        }

        if ($request->hasFile('imagen_referencia')) {
            // Eliminar imagen anterior
            if ($modelo->imagen_referencia) {
                Storage::disk('public')->delete($modelo->imagen_referencia);
            }
            $path = $request->file('imagen_referencia')->store('modelos', 'public');
            $validated['imagen_referencia'] = $path;
        }

        $modelo->update($validated);

        return redirect()
            ->route('catalogo.modelos.index')
            ->with('success', 'Modelo actualizado exitosamente');
    }

    public function destroy(Modelo $modelo)
    {
        // Eliminar imagen si existe
        if ($modelo->imagen_referencia) {
            Storage::disk('public')->delete($modelo->imagen_referencia);
        }

        $modelo->delete();

        return redirect()
            ->route('catalogo.modelos.index')
            ->with('success', 'Modelo eliminado exitosamente');
    }

    /**
     * Creación rápida de modelo desde el formulario de productos (AJAX).
     */
    public function storeRapida(Request $request)
    {
        $validated = $request->validate([
            'nombre'   => 'required|string|max:100',
            'marca_id' => 'required|exists:marcas,id',
        ]);

        $exists = Modelo::where('marca_id', $validated['marca_id'])
            ->where('nombre', $validated['nombre'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un modelo con ese nombre para esta marca.',
            ], 422);
        }

        $modelo = Modelo::create([
            'nombre'   => $validated['nombre'],
            'marca_id' => $validated['marca_id'],
            'estado'   => 'activo',
        ]);

        return response()->json([
            'success' => true,
            'id'      => $modelo->id,
            'nombre'  => $modelo->nombre,
        ]);
    }

    // API para selects dinámicos
    public function getModelosPorMarca($marcaId)
    {
        $modelos = Modelo::where('marca_id', $marcaId)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($modelos);
    }
}