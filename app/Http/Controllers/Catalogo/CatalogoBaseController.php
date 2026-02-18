<?php
// app/Http/Controllers/Catalogo/CatalogoBaseController.php

namespace App\Http\Controllers\Catalogo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class CatalogoBaseController extends Controller
{
    protected $model;
    protected $viewPrefix;
    protected $routePrefix;
    protected $validationRules = [];

    public function index()
    {
        $items = $this->model::orderBy('nombre')->paginate(15);
        return view("catalogo.{$this->viewPrefix}.index", compact('items'));
    }

    public function create()
    {
        return view("catalogo.{$this->viewPrefix}.create");
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules);
        
        $this->model::create($validated);
        
        return redirect()
            ->route("catalogo.{$this->routePrefix}.index")
            ->with('success', 'Registro creado exitosamente');
    }

    public function edit($id)
    {
        $item = $this->model::findOrFail($id);
        return view("catalogo.{$this->viewPrefix}.edit", compact('item'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->validationRules);
        
        $item = $this->model::findOrFail($id);
        $item->update($validated);
        
        return redirect()
            ->route("catalogo.{$this->routePrefix}.index")
            ->with('success', 'Registro actualizado exitosamente');
    }

    public function destroy($id)
    {
        $item = $this->model::findOrFail($id);
        
        // Verificar si tiene relaciones antes de eliminar
        if (method_exists($item, 'canDelete') && !$item->canDelete()) {
            return back()->with('error', 'No se puede eliminar porque tiene registros asociados');
        }
        
        $item->delete();
        
        return redirect()
            ->route("catalogo.{$this->routePrefix}.index")
            ->with('success', 'Registro eliminado exitosamente');
    }
}