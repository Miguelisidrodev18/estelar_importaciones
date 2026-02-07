<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Obtener todos los roles para el formulario
        $roles = Role::all();
        
        return view('auth.register', compact('roles'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'dni' => ['nullable', 'string', 'size:8', 'unique:'.User::class],
            'telefono' => ['nullable', 'string', 'max:15'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ser un correo electrónico válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'dni.size' => 'El DNI debe tener 8 dígitos.',
            'dni.unique' => 'Este DNI ya está registrado.',
            'role_id.required' => 'Debe seleccionar un rol.',
            'role_id.exists' => 'El rol seleccionado no es válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'dni' => $request->dni,
            'telefono' => $request->telefono,
            'role_id' => $request->role_id,
            'password' => Hash::make($request->password),
            'estado' => 'activo',
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Redirigir según el rol
        return $this->redirectBasedOnRole($user);
    }

    /**
     * Redirect user based on their role.
     */
    private function redirectBasedOnRole(User $user): RedirectResponse
    {
        if ($user->hasRole('Administrador')) {
            return redirect()->route('admin.dashboard')->with('success', '¡Bienvenido Administrador!');
        }

        if ($user->hasRole('Vendedor')) {
            return redirect()->route('vendedor.dashboard')->with('success', '¡Bienvenido Vendedor!');
        }

        if ($user->hasRole('Almacenero')) {
            return redirect()->route('almacenero.dashboard')->with('success', '¡Bienvenido Almacenero!');
        }

        if ($user->hasRole('Proveedor')) {
            return redirect()->route('proveedor.dashboard')->with('success', '¡Bienvenido Proveedor!');
        }

        // Por defecto, redirigir al dashboard general
        return redirect()->route('dashboard');
    }
}