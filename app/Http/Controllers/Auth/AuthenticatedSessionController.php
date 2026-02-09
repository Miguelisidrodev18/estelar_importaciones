<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Verificar que el usuario esté activo
        if (auth()->user()->estado !== 'activo') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Tu cuenta está inactiva. Contacta al administrador.',
            ]);
        }

        // Redirigir según el rol del usuario
        $role = auth()->user()->role->nombre;

        return match($role) {
            'Administrador' => redirect()->intended(route('admin.dashboard')),
            'Vendedor' => redirect()->intended(route('vendedor.dashboard')),
            'Almacenero' => redirect()->intended(route('almacenero.dashboard')),
            'Cajero' => redirect()->intended(route('cajero.dashboard')),
            'Proveedor' => redirect()->intended(route('proveedor.dashboard')),
            default => redirect()->intended('/'),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}