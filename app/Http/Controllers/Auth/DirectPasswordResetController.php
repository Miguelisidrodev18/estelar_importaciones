<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules;

class DirectPasswordResetController extends Controller
{
    /**
     * Mostrar el formulario de cambio de contraseña
     */
    public function show(): View
    {
        return view('auth.reset-password-direct');
    }

    /**
     * Procesar el cambio de contraseña
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ser un correo electrónico válido.',
            'email.exists' => 'No existe una cuenta con este correo electrónico.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // Buscar el usuario
        $user = User::where('email', $request->email)->first();

        // Actualizar la contraseña
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Limpiar la verificación de contraseña maestra
        session()->forget('master_password_verified');

        return redirect()->route('login')->with('status', 'Contraseña actualizada correctamente. Ahora puedes iniciar sesión.');
    }
}