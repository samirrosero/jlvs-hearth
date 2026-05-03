<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\HorarioMedico;
use App\Models\Medico;
use App\Models\ModalidadCita;
use App\Models\Paciente;
use App\Models\PrecioServicio;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GestorCitasController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $citas = Cita::where('empresa_id', $empresaId)
            ->with('paciente', 'medico.usuario', 'estado', 'servicio', 'modalidad')
            ->when(request('fecha'), fn ($q) => $q->where('fecha', request('fecha')))
            ->when(request('medico_id'), fn ($q) => $q->where('medico_id', request('medico_id')))
            ->when(request('estado_id'), fn ($q) => $q->where('estado_id', request('estado_id')))
            ->when(request('cedula'), fn ($q) => $q->whereHas('paciente',
                fn ($p) => $p->where('identificacion', 'like', request('cedula') . '%')
            ))
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

        $especialidades = Medico::where('empresa_id', $empresaId)
            ->whereNotNull('especialidad')
            ->distinct()
            ->orderBy('especialidad')
            ->pluck('especialidad');
        $servicios   = Servicio::where('empresa_id', $empresaId)->get();
        $modalidades = ModalidadCita::all();

        // Precios de servicios por portafolio para mostrar al gestor
        $preciosPorPortafolio = PrecioServicio::where('empresa_id', $empresaId)
            ->with('servicio', 'portafolio')
            ->get()
            ->groupBy('servicio_id');

        return view('gestor.citas.create', compact(
            'especialidades', 'servicios', 'modalidades', 'preciosPorPortafolio'
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

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Estado actualizado.']);
        }
        return back()->with('success', 'Estado de la cita actualizado.');
    }

    public function buscarHoy(Request $request)
    {
        $request->validate(['identificacion' => ['required', 'string', 'max:30']]);

        $empresaId = auth()->user()->empresa_id;

        $citas = Cita::where('empresa_id', $empresaId)
            ->whereDate('fecha', today())
            ->where('activo', true)
            ->whereHas('paciente', fn ($q) => $q->where('identificacion', $request->identificacion))
            ->with('paciente', 'medico.usuario', 'estado', 'servicio')
            ->orderBy('hora')
            ->get();

        return response()->json($citas);
    }

    /**
     * Agenda una cita auto-asignando el médico con menor carga
     * (mismo flujo que el portal del paciente, pero el gestor elige el paciente).
     */
    public function agendar(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'especialidad' => ['required', 'string', 'max:100'],
            'fecha'        => ['required', 'date', 'after_or_equal:today'],
            'hora'         => ['required', 'date_format:H:i'],
            'modalidad_id' => ['required', 'exists:modalidades_cita,id'],
            'paciente_id'  => ['required', 'exists:pacientes,id'],
            'servicio_id'  => ['nullable', 'exists:servicios,id'],
        ]);

        abort_unless(
            Paciente::where('id', $data['paciente_id'])->where('empresa_id', $empresaId)->exists(),
            403
        );

        $fecha     = Carbon::parse($data['fecha']);
        $hora      = $data['hora'];
        $diaSemana = (int) $fecha->format('w');
        $servicio  = isset($data['servicio_id']) ? Servicio::find($data['servicio_id']) : null;
        $duracion  = $servicio?->duracion_minutos ?? 30;
        $horaFin   = Carbon::parse("{$data['fecha']} {$hora}")->addMinutes($duracion)->format('H:i:s');

        $medicosIds = Medico::where('empresa_id', $empresaId)
            ->where('especialidad', 'like', "%{$data['especialidad']}%")
            ->pluck('id');

        if ($medicosIds->isEmpty()) {
            return back()->withInput()->with('error', 'No hay médicos registrados para esa especialidad.');
        }

        $medicosConHorario = HorarioMedico::whereIn('medico_id', $medicosIds)
            ->where('dia_semana', $diaSemana)
            ->where('hora_inicio', '<=', $hora)
            ->where('hora_fin', '>=', $horaFin)
            ->where('activo', true)
            ->pluck('medico_id')->unique();

        $medicosOcupados = Cita::whereIn('medico_id', $medicosConHorario)
            ->whereDate('fecha', $fecha->toDateString())
            ->whereNotIn('estado_id', [4, 5])
            ->where('activo', true)
            ->whereRaw('hora < ?', [$horaFin])
            ->whereRaw('ADDTIME(hora, SEC_TO_TIME(? * 60)) > ?', [$duracion, "{$hora}:00"])
            ->pluck('medico_id')->unique();

        $medicosLibres = $medicosConHorario->diff($medicosOcupados);

        if ($medicosLibres->isEmpty()) {
            return back()->withInput()->with('error', 'Ese cupo ya fue tomado. Selecciona otro horario.');
        }

        $cargaPorMedico = Cita::whereIn('medico_id', $medicosLibres)
            ->whereDate('fecha', $fecha->toDateString())
            ->where('activo', true)
            ->selectRaw('medico_id, COUNT(*) as total')
            ->groupBy('medico_id')
            ->pluck('total', 'medico_id');

        $medicoId = $medicosLibres->sortBy(fn($id) => $cargaPorMedico[$id] ?? 0)->first();

        Cita::create([
            'empresa_id'   => $empresaId,
            'medico_id'    => $medicoId,
            'paciente_id'  => $data['paciente_id'],
            'estado_id'    => 1,
            'modalidad_id' => $data['modalidad_id'],
            'servicio_id'  => $data['servicio_id'] ?? null,
            'fecha'        => $data['fecha'],
            'hora'         => $hora,
            'activo'       => true,
        ]);

        return redirect()->route('gestor.citas')->with('exito', 'Cita agendada correctamente.');
    }
}
