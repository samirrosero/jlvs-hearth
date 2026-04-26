<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PacientePerfilController extends Controller
{
    public function edit()
    {
        $user     = auth()->user();
        $paciente = $user->paciente;

        return view('paciente.perfil.edit', compact('user', 'paciente'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user     = auth()->user();
        $paciente = $user->paciente;

        $request->validate([
            'telefono'        => ['nullable', 'max:20'],
            'correo'          => ['nullable', 'email', 'max:150'],
            'password_actual' => ['required_with:password', 'nullable'],
            'password'        => ['nullable', 'confirmed', Password::min(8)],
        ], [
            'correo.email'                  => 'El correo no tiene un formato válido.',
            'password_actual.required_with' => 'Debes ingresar tu contraseña actual para cambiarla.',
            'password.confirmed'            => 'La confirmación de la nueva contraseña no coincide.',
            'password.min'                  => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $paciente->update([
            'telefono' => $request->telefono,
            'correo'   => $request->correo,
        ]);

        if ($request->filled('password')) {
            if (! Hash::check($request->password_actual, $user->password)) {
                return back()
                    ->withInput()
                    ->withErrors(['password_actual' => 'La contraseña actual es incorrecta.']);
            }

            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Perfil actualizado correctamente.');
    }
}
