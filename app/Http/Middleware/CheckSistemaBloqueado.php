<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSistemaBloqueado
{
    public function handle(Request $request, Closure $next): Response
    {
        if (env('SISTEMA_BLOQUEADO', false) === true || env('SISTEMA_BLOQUEADO', 'false') === 'true') {
            // Permitir rutas de auth y la propia vista de bloqueo
            if ($request->routeIs('sistema.bloqueado') || $request->routeIs('logout') || $request->routeIs('login')) {
                return $next($request);
            }
            return redirect()->route('sistema.bloqueado');
        }

        return $next($request);
    }
}
