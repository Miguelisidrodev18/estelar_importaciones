<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verificar si el usuario está autenticado
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder a esta página.');
        }

        // Obtener el usuario autenticado
        $user = auth()->user();

        // Verificar si el usuario tiene rol asignado
        if (!$user->role) {
            abort(403, 'No tienes un rol asignado. Contacta al administrador.');
        }

        // Verificar si el usuario tiene alguno de los roles permitidos
        if (!empty($roles) && !in_array($user->role->nombre, $roles)) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        return $next($request);
    }
}