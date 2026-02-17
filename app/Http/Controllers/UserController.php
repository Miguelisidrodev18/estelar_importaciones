<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Almacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $users = User::with('role') // Asegúrate de cargar la relación
        ->orderBy('name')
        ->paginate(15);
    
    // Para debug: verificar usuarios sin rol
    $usersSinRol = User::whereNull('role_id')->orWhereDoesntHave('role')->get();
    if ($usersSinRol->count() > 0) {
        // Puedes loguearlo o mostrarlo temporalmente
        \Log::warning('Usuarios sin rol: ' . $usersSinRol->pluck('name'));
    }
    
    return view('users.index', compact('users'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::orderBy('nombre')->get();
        $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();
        
        return view('users.create', compact('roles', 'almacenes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'dni' => 'nullable|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'almacen_id' => 'nullable|exists:almacenes,id',
            'estado' => 'required|in:activo,inactivo',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $validated['password'] = Hash::make($validated['password']);
            
            User::create($validated);

            DB::commit();

            return redirect()
                ->route('users.index')
                ->with('success', 'Usuario creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error al crear el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('role', 'almacen');
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::orderBy('nombre')->get();
        $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();
        
        return view('users.edit', compact('user', 'roles', 'almacenes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'dni' => 'nullable|string|max:20|unique:users,dni,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'almacen_id' => 'nullable|exists:almacenes,id',
            'estado' => 'required|in:activo,inactivo',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            DB::commit();

            return redirect()
                ->route('users.index')
                ->with('success', 'Usuario actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Evitar que se elimine a sí mismo
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        try {
            DB::beginTransaction();
            
            $user->delete();
            
            DB::commit();

            return redirect()
                ->route('users.index')
                ->with('success', 'Usuario eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }
}