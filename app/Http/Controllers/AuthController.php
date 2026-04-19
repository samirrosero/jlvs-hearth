<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login    = trim($request->input('login'));
        $esCorreo = str_contains($login, '@');

        $credenciales = $esCorreo
            ? ['email'          => $login, 'password' => $request->input('password')]
            : ['identificacion' => $login, 'password' => $request->input('password')];

        if (!Auth::attempt($credenciales, $request->boolean('remember'))) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        // Rechazar usuarios desactivados por el administrador
        if (!auth()->user()->activo) {
            Auth::logout();
            return response()->json(['message' => 'Tu cuenta ha sido desactivada. Contacta al administrador.'], 403);
        }

        $request->session()->regenerate();

        $usuario = auth()->user()->load('rol', 'empresa');

        return response()->json([
            'message'               => 'Sesión iniciada.',
            'usuario'               => $usuario,
            'debe_cambiar_password' => (bool) $usuario->debe_cambiar_password,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    public function me(): JsonResponse
    {
        return response()->json(auth()->user()->load('rol', 'empresa'));
    }
}
