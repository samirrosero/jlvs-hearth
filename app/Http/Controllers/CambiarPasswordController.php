<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class CambiarPasswordController extends Controller
{
    /**
     * El usuario autenticado cambia su propia contraseña.
     * Si tenía debe_cambiar_password = true (contraseña temporal),
     * se resetea a false tras el cambio exitoso.
     *
     * POST /mi-cuenta/cambiar-password
     * Body: { password_actual, password, password_confirmation }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'password_actual'      => ['required', 'string'],
            'password'             => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $usuario = auth()->user();

        // Verificar que la contraseña actual sea correcta
        if (!Hash::check($request->input('password_actual'), $usuario->password)) {
            throw ValidationException::withMessages([
                'password_actual' => ['La contraseña actual no es correcta.'],
            ]);
        }

        // Evitar reutilizar la misma contraseña
        if (Hash::check($request->input('password'), $usuario->password)) {
            throw ValidationException::withMessages([
                'password' => ['La nueva contraseña no puede ser igual a la actual.'],
            ]);
        }

        $usuario->update([
            'password'              => $request->input('password'),  // cast 'hashed' la cifra
            'debe_cambiar_password' => false,
        ]);

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }
}
