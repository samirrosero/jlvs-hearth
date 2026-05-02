<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\Medico;
use App\Models\ModalidadCita;
use App\Models\Paciente;
use App\Models\Servicio;
use Illuminate\Http\Request;

class GestorCitasController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $citas = Cita::where('empresa_id', $empresaId)
            ->with('paciente', 'medico.usuario', 'estado', 'servicio')
            ->when(request('fecha'), fn ($q) => $q->where('fecha', request('fecha')))
            ->when(request('medico_id'), fn ($q) => $q->where('medico_id', request('medico_id')))
            ->when(request('estado_id'), fn ($q) => $q->where('estado_id', request('estado_id')))
            ->orderByDesc('fecha')->orderByDesc('hora')
            ->paginate(15)
            ->withQueryString();

        $estados = EstadoCita::all();
        $medicos = Medico::where('empresa_id', $empresaId)->with('usuario')->get();

        return view('gestor.citas.index', compact('citas', 'estados', 'medicos'));
    }

    public function create()
    {
        $empresaId = auth()->user()->empresa_id;

        // Datos para poblar el formulario
        $pacientes  = Paciente::where('empresa_id', $empresaId)->orderBy('nombre_completo')->get();
        $medicos    = Medico::where('empresa_id', $empresaId)->with('usuario')->get();
        $servicios  = Servicio::where('empresa_id', $empresaId)->get();
        $modalidades = ModalidadCita::all();
        $estados    = EstadoCita::all();

        return view('gestor.citas.create', compact(
            'pacientes', 'medicos', 'servicios', 'modalidades', 'estados'
        ));
    }

    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'paciente_id'  => ['required', 'exists:pacientes,id'],
            'medico_id'    => ['required', 'exists:medicos,id'],
            'servicio_id'  => ['nullable', 'exists:servicios,id'],
            'modalidad_id' => ['nullable', 'exists:modalidades_cita,id'],
            'estado_id'    => ['required', 'exists:estados_cita,id'],
            'fecha'        => ['required', 'date'],
            'hora'         => ['required', 'date_format:H:i'],
        ]);

        // Verificar que médico y paciente son de esta empresa
        abort_unless(
            Medico::where('id', $data['medico_id'])->where('empresa_id', $empresaId)->exists() &&
            Paciente::where('id', $data['paciente_id'])->where('empresa_id', $empresaId)->exists(),
            403
        );

        Cita::create(array_merge($data, ['empresa_id' => $empresaId, 'activo' => true]));

        return redirect()->route('gestor.citas')->with('exito', 'Cita agendada correctamente.');
    }

    public function edit(Cita $cita)
    {
        abort_if($cita->empresa_id !== auth()->user()->empresa_id, 403);

        $empresaId   = auth()->user()->empresa_id;
        $pacientes   = Paciente::where('empresa_id', $empresaId)->orderBy('nombre_completo')->get();
        $medicos     = Medico::where('empresa_id', $empresaId)->with('usuario')->get();
        $servicios   = Servicio::where('empresa_id', $empresaId)->get();
        $modalidades = ModalidadCita::all();
        $estados     = EstadoCita::all();
        $cita->load('paciente', 'medico', 'estado');

        return view('gestor.citas.edit', compact(
            'cita', 'pacientes', 'medicos', 'servicios', 'modalidades', 'estados'
        ));
    }

    public function update(Request $request, Cita $cita)
    {
        abort_if($cita->empresa_id !== auth()->user()->empresa_id, 403);

        $data = $request->validate([
            'paciente_id'  => ['required', 'exists:pacientes,id'],
            'medico_id'    => ['required', 'exists:medicos,id'],
            'servicio_id'  => ['nullable', 'exists:servicios,id'],
            'modalidad_id' => ['nullable', 'exists:modalidades_cita,id'],
            'estado_id'    => ['required', 'exists:estados_cita,id'],
            'fecha'        => ['required', 'date'],
            'hora'         => ['required', 'date_format:H:i'],
        ]);

        $cita->update($data);

        return redirect()->route('gestor.citas')->with('exito', 'Cita actualizada correctamente.');
    }

    public function cambiarEstado(Request $request, Cita $cita)
    {
        abort_if($cita->empresa_id !== auth()->user()->empresa_id, 403);

        $data = $request->validate(['estado_id' => ['required', 'exists:estados_cita,id']]);
        $cita->update($data);

        return back()->with('success', 'Estado de la cita actualizado.');
    }
}