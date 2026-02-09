<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MasterPasswordController extends Controller
{
    /**
     * Contraseña maestra del sistema
     * IMPORTANTE: En producción, esto debería estar en el .env
     */
    private function getMasterPassword(): string
    {
    return env('MASTER_PASSWORD', 'ImportMaster2024');
    }
    public function show(Request $request): View
    {
        $redirect = $request->query('redirect', 'register');
        
        return view('auth.master-password', compact('redirect'));
    }

    /**
     * Verificar la contraseña maestra
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'master_password' => ['required', 'string'],
            'redirect' => ['required', 'string'],
        ], [
            'master_password.required' => 'La contraseña maestra es obligatoria.',
        ]);

        // Verificar la contraseña maestra
        if ($request->master_password !== $this->getMasterPassword()) {
            return back()->withErrors([
                'master_password' => 'La contraseña maestra es incorrecta.',
            ])->withInput();
        }

        // Guardar en sesión que la contraseña maestra fue verificada
        session(['master_password_verified' => true]);

        // Redirigir a la ruta original
        return redirect($request->redirect);
    }
}