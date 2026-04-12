<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medico;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminMedicoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $query = Medico::where('medicos.empresa_id', $empresaId)->with('usuario');

        if ($request->filled('buscar')) {
            $t = $request->buscar;
            $query->where(fn ($q) =>
                $q->where('especialidad', 'like', "%{$t}%")
                  ->orWhereHas('usuario', fn ($u) => $u->where('nombre', 'like', "%{$t}%"))
            );
        }

        $medicos = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.medicos.index', compact('medicos'));
    }

    public function create()
    {
        return view('admin.medicos.create');
    }

    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $request->validate([
            'nombre'          => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email', 'unique:users,email'],
            'identificacion'  => ['required', 'string', 'max:20', Rule::unique('users')->where('empresa_id', $empresaId)],
            'password'        => ['required', 'string', 'min:8', 'confirmed'],
            'especialidad'    => ['required', 'string', 'max:100'],
            'registro_medico' => ['required', 'string', 'max:50', 'unique:medicos,registro_medico'],
        ]);

        DB::transaction(function () use ($request, $empresaId) {
            $rolMedico = Rol::where('nombre', 'medico')->firstOrFail();

            $usuario = User::create([
                'empresa_id'     => $empresaId,
                'rol_id'         => $rolMedico->id,
                'nombre'         => $request->nombre,
                'email'          => $request->email,
                'identificacion' => $request->identificacion,
                'password'       => Hash::make($request->password),
                'activo'         => true,
            ]);

            Medico::create([
                'empresa_id'     => $empresaId,
                'usuario_id'     => $usuario->id,
                'especialidad'   => $request->especialidad,
                'registro_medico' => $request->registro_medico,
            ]);
        });

        return redirect()->route('admin.medicos.index')
            ->with('exito', 'Médico registrado correctamente.');
    }

    public function edit(Medico $medico)
    {
        abort_if($medico->empresa_id !== auth()->user()->empresa_id, 403);
        $medico->load('usuario');
        return view('admin.medicos.edit', compact('medico'));
    }

    public function update(Request $request, Medico $medico)
    {
        abort_if($medico->empresa_id !== auth()->user()->empresa_id, 403);

        $request->validate([
            'nombre'          => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email', Rule::unique('users', 'email')->ignore($medico->usuario_id)],
            'especialidad'    => ['required', 'string', 'max:100'],
            'registro_medico' => ['required', 'string', 'max:50', Rule::unique('medicos', 'registro_medico')->ignore($medico->id)],
        ]);

        DB::transaction(function () use ($request, $medico) {
            $medico->usuario->update([
                'nombre' => $request->nombre,
                'email'  => $request->email,
            ]);

            $medico->update([
                'especialidad'    => $request->especialidad,
                'registro_medico' => $request->registro_medico,
            ]);
        });

        return redirect()->route('admin.medicos.index')
            ->with('exito', 'Médico actualizado correctamente.');
    }

    public function destroy(Medico $medico)
    {
        abort_if($medico->empresa_id !== auth()->user()->empresa_id, 403);
        // Desactivar el usuario en lugar de borrar para preservar historial
        $medico->usuario->update(['activo' => false]);
        $medico->delete();
        return redirect()->route('admin.medicos.index')
            ->with('exito', 'Médico eliminado.');
    }
}
