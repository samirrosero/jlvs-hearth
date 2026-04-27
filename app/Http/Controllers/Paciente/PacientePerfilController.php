<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Portafolio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PacientePerfilController extends Controller
{
    public function edit()
    {
        $user        = auth()->user();
        $paciente    = $user->paciente;
        $portafolios = Portafolio::where('empresa_id', $user->empresa_id)
            ->orderBy('nombre_convenio')->get();

        return view('paciente.perfil.edit', compact('user', 'paciente', 'portafolios'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user     = auth()->user();
        $paciente = $user->paciente;

        $request->validate([
            'telefono'          => ['nullable', 'max:20'],
            'correo'            => ['nullable', 'email', 'max:150'],
            'portafolio_id'     => ['nullable', 'exists:portafolios,id'],
            'nombre_aseguradora'=> ['nullable', 'string', 'max:100'],
            'numero_poliza'     => ['nullable', 'string', 'max:60'],
            'password_actual'   => ['nullable', 'required_with:password'],
            'password'          => ['nullable', 'confirmed', Password::min(8)],
        ], [
            'correo.email'                  => 'El correo no tiene un formato válido.',
            'password_actual.required_with' => 'Debes ingresar tu contraseña actual para cambiarla.',
            'password.confirmed'            => 'La confirmación de la nueva contraseña no coincide.',
            'password.min'                  => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $paciente->update([
            'telefono'          => $request->telefono,
            'correo'            => $request->correo,
            'portafolio_id'     => $request->portafolio_id ?: null,
            'nombre_aseguradora'=> $request->nombre_aseguradora,
            'numero_poliza'     => $request->numero_poliza,
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
