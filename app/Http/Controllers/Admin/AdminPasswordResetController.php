<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AdminPasswordResetController extends Controller
{
    // GET /admin/forgot-password
    public function showForgot()
    {
        return view('admin.auth.forgot-password');
    }

    // POST /admin/forgot-password
    public function sendLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // Apuntar el enlace del correo a nuestra ruta del panel admin
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return route('admin.reset-password', ['token' => $token, 'email' => $user->email]);
        });

        $estado = Password::sendResetLink($request->only('email'));

        if ($estado === Password::RESET_LINK_SENT) {
            return back()->with('exito', 'Te enviamos un enlace a tu correo. Revisa tu bandeja de entrada.');
        }

        return back()->withErrors(['email' => __($estado)])->withInput();
    }

    // GET /admin/reset-password/{token}
    public function showReset(Request $request, string $token)
    {
        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    // POST /admin/reset-password
    public function reset(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $estado = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($estado === Password::PASSWORD_RESET) {
            return redirect()->route('admin.login')
                ->with('exito', 'Contraseña restablecida correctamente. Ya puedes iniciar sesión.');
        }

        return back()->withErrors(['email' => __($estado)])->withInput();
    }
}
