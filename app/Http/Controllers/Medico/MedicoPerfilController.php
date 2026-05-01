<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MedicoPerfilController extends Controller
{
    public function edit(): View
    {
        $user   = auth()->user();
        $medico = $user->medico;

        return view('medico.perfil.edit', compact('user', 'medico'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user   = auth()->user();
        $medico = $user->medico;

        $request->validate([
            'nombre'           => ['required', 'string', 'max:150'],
            'email'            => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'especialidad'     => ['nullable', 'string', 'max:150'],
            'registro_medico'  => ['nullable', 'string', 'max:50'],
        ]);

        $user->update([
            'nombre' => $request->nombre,
            'email'  => $request->email,
        ]);

        $medico->update([
            'especialidad'    => $request->especialidad,
            'registro_medico' => $request->registro_medico,
        ]);

        return back()->with('exito', 'Perfil actualizado correctamente.');
    }
}
