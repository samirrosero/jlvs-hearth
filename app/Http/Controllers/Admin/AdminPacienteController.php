<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;

class AdminPacienteController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $query = Paciente::where('empresa_id', $empresaId);

        if ($request->filled('buscar')) {
            $t = $request->buscar;
            $query->where(fn ($q) =>
                $q->where('nombre_completo', 'like', "%{$t}%")
                  ->orWhere('identificacion', 'like', "%{$t}%")
            );
        }
        if ($request->filled('sexo')) {
            $query->where('sexo', $request->sexo);
        }

        if ($request->filled('edad')) {
            $hoy = now();
            match ($request->edad) {
                '0-17'  => $query->where('fecha_nacimiento', '>', $hoy->copy()->subYears(18)),
                '18-40' => $query->whereBetween('fecha_nacimiento', [$hoy->copy()->subYears(41), $hoy->copy()->subYears(18)]),
                '41-65' => $query->whereBetween('fecha_nacimiento', [$hoy->copy()->subYears(66), $hoy->copy()->subYears(41)]),
                '65+'   => $query->where('fecha_nacimiento', '<=', $hoy->copy()->subYears(65)),
                default => null,
            };
        }

        $pacientes = $query->orderBy('nombre_completo')->paginate(10)->withQueryString();

        return view('admin.pacientes.index', compact('pacientes'));
    }

    public function create()
    {
        return view('admin.pacientes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_completo'  => ['required', 'string', 'max:150'],
            'identificacion'   => ['required', 'string', 'max:20',
                \Illuminate\Validation\Rule::unique('pacientes')->where('empresa_id', auth()->user()->empresa_id)],
            'fecha_nacimiento' => ['required', 'date'],
            'sexo'             => ['required', 'in:M,F,Otro'],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'correo'           => ['nullable', 'email', 'max:100'],
            'direccion'        => ['nullable', 'string', 'max:255'],
        ]);

        $data['empresa_id'] = auth()->user()->empresa_id;
        Paciente::create($data);

        return redirect()->route('admin.pacientes.index')
            ->with('exito', 'Paciente registrado correctamente.');
    }

    public function edit(Paciente $paciente)
    {
        abort_if($paciente->empresa_id !== auth()->user()->empresa_id, 403);
        return view('admin.pacientes.edit', compact('paciente'));
    }

    public function update(Request $request, Paciente $paciente)
    {
        abort_if($paciente->empresa_id !== auth()->user()->empresa_id, 403);

        $data = $request->validate([
            'nombre_completo'  => ['required', 'string', 'max:150'],
            'identificacion'   => ['required', 'string', 'max:20',
                \Illuminate\Validation\Rule::unique('pacientes')->where('empresa_id', auth()->user()->empresa_id)->ignore($paciente->id)],
            'fecha_nacimiento' => ['required', 'date'],
            'sexo'             => ['required', 'in:M,F,Otro'],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'correo'           => ['nullable', 'email', 'max:100'],
            'direccion'        => ['nullable', 'string', 'max:255'],
        ]);

        $paciente->update($data);

        return redirect()->route('admin.pacientes.index')
            ->with('exito', 'Paciente actualizado correctamente.');
    }

    public function destroy(Paciente $paciente)
    {
        abort_if($paciente->empresa_id !== auth()->user()->empresa_id, 403);

        if ($paciente->citas()->exists()) {
            return redirect()->route('admin.pacientes.index')
                ->with('error', 'No se puede eliminar el paciente porque tiene citas registradas.');
        }

        $paciente->delete();
        return redirect()->route('admin.pacientes.index')
            ->with('exito', 'Paciente eliminado.');
    }
}
