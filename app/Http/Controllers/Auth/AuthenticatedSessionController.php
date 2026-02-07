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

        // Obtener el usuario autenticado
        $user = Auth::user();

        // Verificar si el usuario está activo
        if ($user->estado !== 'activo') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Tu cuenta está inactiva. Contacta al administrador.',
            ]);
        }

        // Redirigir según el rol
        return $this->redirectBasedOnRole($user);
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

    /**
     * Redirect user based on their role.
     */
    private function redirectBasedOnRole($user): RedirectResponse
    {
        if ($user->hasRole('Administrador')) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->hasRole('Vendedor')) {
            return redirect()->intended(route('vendedor.dashboard'));
        }

        if ($user->hasRole('Almacenero')) {
            return redirect()->intended(route('almacenero.dashboard'));
        }

        if ($user->hasRole('Proveedor')) {
            return redirect()->intended(route('proveedor.dashboard'));
        }

        // Por defecto
        return redirect()->intended(route('dashboard'));
    }
}