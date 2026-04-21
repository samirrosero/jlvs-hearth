<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Verifica que el usuario autenticado tenga uno de los roles permitidos.
     * Uso en rutas: middleware('role:administrador,medico')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return $request->expectsJson()
                ? response()->json(['message' => 'No autenticado.'], 401)
                : redirect()->route('login');
        }

        if (!$usuario->activo) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Tu cuenta ha sido desactivada. Contacta al administrador.'], 403)
                : back()->withErrors(['login' => 'Tu cuenta ha sido desactivada. Contacta al administrador.']);
        }

        if (!in_array($usuario->rol?->nombre, $roles)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'No tienes permiso para realizar esta acción.'], 403)
                : abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
