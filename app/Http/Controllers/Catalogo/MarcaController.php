<?php
// app/Http/Controllers/Catalogo/MarcaController.php

namespace App\Http\Controllers\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\Marca;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = Marca::withCount('modelos')
            ->with('categorias')
            ->orderBy('nombre')
            ->paginate(15);

        return view('catalogo.marcas.index', compact('marcas'));
    }

    public function create()
    {
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre')->get();
        return view('catalogo.marcas.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'       => 'required|string|max:100|unique:marcas',
            'descripcion'  => 'nullable|string',
            'sitio_web'    => 'nullable|url|max:255',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'estado'       => 'required|in:activo,inactivo',
            'categorias'   => 'nullable|array',
            'categorias.*' => 'exists:categorias,id',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('marcas', 'public');
        }

        $marca = Marca::create($validated);
        $marca->categorias()->sync($request->input('categorias', []));

        return redirect()
            ->route('catalogo.marcas.index')
            ->with('success', 'Marca creada exitosamente');
    }

    public function edit(Marca $marca)
    {
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre')->get();
        $marca->load('categorias');
        return view('catalogo.marcas.edit', compact('marca', 'categorias'));
    }

    public function update(Request $request, Marca $marca)
    {
        $validated = $request->validate([
            'nombre'       => 'required|string|max:100|unique:marcas,nombre,' . $marca->id,
            'descripcion'  => 'nullable|string',
            'sitio_web'    => 'nullable|url|max:255',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'estado'       => 'required|in:activo,inactivo',
            'categorias'   => 'nullable|array',
            'categorias.*' => 'exists:categorias,id',
        ]);

        if ($request->hasFile('logo')) {
            if ($marca->logo) {
                Storage::disk('public')->delete($marca->logo);
            }
            $validated['logo'] = $request->file('logo')->store('marcas', 'public');
        }

        $marca->update($validated);
        $marca->categorias()->sync($request->input('categorias', []));

        return redirect()
            ->route('catalogo.marcas.index')
            ->with('success', 'Marca actualizada exitosamente');
    }

    public function destroy(Marca $marca)
    {
        if ($marca->modelos()->exists()) {
            return back()->with('error', 'No se puede eliminar porque tiene modelos asociados');
        }

        if ($marca->logo) {
            Storage::disk('public')->delete($marca->logo);
        }

        $marca->delete();

        return redirect()
            ->route('catalogo.marcas.index')
            ->with('success', 'Marca eliminada exitosamente');
    }

    /**
     * API: devuelve las marcas activas que pertenecen a una categoría.
     * Usado por el selector encadenado Categoría → Marca → Modelo en productos.
     */
    public function getMarcasPorCategoria($categoriaId)
    {
        $categoria = Categoria::findOrFail($categoriaId);

        $marcas = $categoria->marcas()
            ->where('marcas.estado', 'activo')
            ->orderBy('marcas.nombre')
            ->get(['marcas.id', 'marcas.nombre']);

        // Si la categoría no tiene marcas vinculadas, devolver todas las activas
        if ($marcas->isEmpty()) {
            $marcas = Marca::where('estado', 'activo')
                ->orderBy('nombre')
                ->get(['id', 'nombre']);
        }

        return response()->json($marcas);
    }
}
