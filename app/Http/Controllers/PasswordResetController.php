<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Paso 1: El usuario ingresa su email.
     * Laravel genera el token y envía el correo automáticamente.
     *
     * POST /forgot-password
     */
    public function enviarEnlace(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $estado = Password::sendResetLink(
            $request->only('email')
        );

        if ($estado !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($estado)],
            ]);
        }

        return response()->json([
            'message' => 'Te enviamos un enlace para restablecer tu contraseña. Revisa tu correo.',
        ]);
    }

    /**
     * Paso 2: El usuario llega con el token del email y establece nueva contraseña.
     *
     * POST /reset-password
     */
    public function resetear(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $estado = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($estado !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($estado)],
            ]);
        }

        return response()->json([
            'message' => 'Contraseña restablecida correctamente. Ya puedes iniciar sesión.',
        ]);
    }
}
