<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMasterPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si ya se validó la contraseña maestra en esta sesión
        if (!session()->has('master_password_verified')) {
            // Redirigir a la página de verificación de contraseña maestra
            return redirect()->route('master-password.show', [
                'redirect' => $request->path()
            ]);
        }

        return $next($request);
    }
}