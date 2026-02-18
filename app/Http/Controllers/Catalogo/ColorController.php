<?php
// app/Http/Controllers/Catalogo/ColorController.php

namespace App\Http\Controllers\Catalogo;

use App\Models\Catalogo\Color;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ColorController extends Controller
{
    public function index()
    {
        $colores = Color::orderBy('nombre')->paginate(15);
        return view('catalogo.colores.index', compact('colores'));
    }

    public function create()
    {
        return view('catalogo.colores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:colores',
            'codigo_hex' => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            'codigo_color' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:activo,inactivo'
        ]);

        Color::create($validated);

        return redirect()
            ->route('catalogo.colores.index')
            ->with('success', 'Color creado exitosamente');
    }

    public function edit(Color $color)
    {
        return view('catalogo.colores.edit', compact('color'));
    }

    public function update(Request $request, Color $color)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:colores,nombre,' . $color->id,
            'codigo_hex' => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            'codigo_color' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:activo,inactivo'
        ]);

        $color->update($validated);

        return redirect()
            ->route('catalogo.colores.index')
            ->with('success', 'Color actualizado exitosamente');
    }

    public function destroy(Color $color)
    {
        // Verificar si el color está siendo usado
        // if ($color->productos()->exists()) {
        //     return back()->with('error', 'No se puede eliminar porque tiene productos asociados');
        // }

        $color->delete();

        return redirect()
            ->route('catalogo.colores.index')
            ->with('success', 'Color eliminado exitosamente');
    }
}