<?php
// app/Http/Controllers/Catalogo/MarcaController.php

namespace App\Http\Controllers\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = Marca::withCount('modelos')->orderBy('nombre')->paginate(15);
        return view('catalogo.marcas.index', compact('marcas'));
    }

    public function create()
    {
        return view('catalogo.marcas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:marcas',
            'descripcion' => 'nullable|string',
            'sitio_web' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'estado' => 'required|in:activo,inactivo'
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('marcas', 'public');
            $validated['logo'] = $path;
        }

        Marca::create($validated);

        return redirect()
            ->route('catalogo.marcas.index')
            ->with('success', 'Marca creada exitosamente');
    }

    public function edit(Marca $marca)
    {
        return view('catalogo.marcas.edit', compact('marca'));
    }

    public function update(Request $request, Marca $marca)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:marcas,nombre,' . $marca->id,
            'descripcion' => 'nullable|string',
            'sitio_web' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'estado' => 'required|in:activo,inactivo'
        ]);

        if ($request->hasFile('logo')) {
            // Eliminar logo anterior
            if ($marca->logo) {
                Storage::disk('public')->delete($marca->logo);
            }
            $path = $request->file('logo')->store('marcas', 'public');
            $validated['logo'] = $path;
        }

        $marca->update($validated);

        return redirect()
            ->route('catalogo.marcas.index')
            ->with('success', 'Marca actualizada exitosamente');
    }

    public function destroy(Marca $marca)
    {
        // Verificar si tiene modelos asociados
        if ($marca->modelos()->exists()) {
            return back()->with('error', 'No se puede eliminar porque tiene modelos asociados');
        }

        // Eliminar logo si existe
        if ($marca->logo) {
            Storage::disk('public')->delete($marca->logo);
        }

        $marca->delete();

        return redirect()
            ->route('catalogo.marcas.index')
            ->with('success', 'Marca eliminada exitosamente');
    }
}