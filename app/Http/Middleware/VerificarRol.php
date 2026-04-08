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
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        if (!$usuario->activo) {
            return response()->json(['message' => 'Tu cuenta ha sido desactivada. Contacta al administrador.'], 403);
        }

        if (!in_array($usuario->rol?->nombre, $roles)) {
            return response()->json(['message' => 'No tienes permiso para realizar esta acción.'], 403);
        }

        return $next($request);
    }
}
