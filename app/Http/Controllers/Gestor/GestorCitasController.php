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
    // Estados que liberan el slot (cancelada=4, no asistió=5)
    private const ESTADOS_LIBERAN = [4, 5];

    private function isHorarioDisponible(int $medicoId, string $fecha, string $hora, ?int $servicioId = null, ?int $excludeCitaId = null): bool
    {
        $fechaCarbon = Carbon::parse($fecha);
        $diaSemana = (int) $fechaCarbon->format('N') % 7;

        $servicio = $servicioId ? Servicio::find($servicioId) : null;
        $duracionMinutos = $servicio?->duracion_minutos ?? 30;

        // Horarios del médico para ese día de la semana
        $horarios = HorarioMedico::where('medico_id', $medicoId)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->get();

        if ($horarios->isEmpty()) {
            return false;
        }

        // Citas ya ocupadas ese día (excluir canceladas y no asistió, y excluir la cita actual si es update)
        $query = Cita::where('medico_id', $medicoId)
            ->whereDate('fecha', $fechaCarbon->toDateString())
            ->whereNotIn('estado_id', self::ESTADOS_LIBERAN)
            ->where('activo', true);

        if ($excludeCitaId) {
            $query->where('id', '!=', $excludeCitaId);
        }

        $horasOcupadas = $query->pluck('hora')
            ->map(fn ($h) => substr($h, 0, 5)) // HH:MM
            ->toArray();

        // Generar slots disponibles
        $slotsDisponibles = [];
        foreach ($horarios as $horario) {
            $cursor = Carbon::parse($fechaCarbon->toDateString() . ' ' . $horario->hora_inicio);
            $fin = Carbon::parse($fechaCarbon->toDateString() . ' ' . $horario->hora_fin);

            while ($cursor->copy()->addMinutes($duracionMinutos)->lte($fin)) {
                $horaSlot = $cursor->format('H:i');
                if (!in_array($horaSlot, $horasOcupadas)) {
                    $slotsDisponibles[] = $horaSlot;
                }
                $cursor->addMinutes($duracionMinutos);
            }
        }

        return in_array($hora, $slotsDisponibles);
    }

    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $query = Cita::where('empresa_id', $empresaId)
            ->with('paciente', 'medico.usuario', 'estado', 'servicio', 'modalidad')
            ->when(request('fecha'), fn ($q) => $q->where('fecha', request('fecha')))
            ->when(request('medico_id'), fn ($q) => $q->where('medico_id', request('medico_id')))
            ->when(request('estado_id'), fn ($q) => $q->where('estado_id', request('estado_id')))
            ->when(request('cedula'), fn ($q) => $q->whereHas('paciente',
                fn ($p) => $p->where('identificacion', 'like', request('cedula') . '%')
            ));

        $citas = $query->clone()
            ->orderByDesc('fecha')->orderByDesc('hora')
            ->paginate(15)
            ->withQueryString();

        $citasPorMedico = $query->clone()
            ->orderBy('medico_id')
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get()
            ->groupBy(fn ($cita) => $cita->medico?->id ?? 'sin_medico');

        $estados = EstadoCita::all();
        $medicos = Medico::where('empresa_id', $empresaId)->with('usuario')->orderBy('usuario_id')->get();

        return view('gestor.citas.index', compact('citas', 'estados', 'medicos', 'citasPorMedico'));
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

        // Verificar disponibilidad del horario
        if (!$this->isHorarioDisponible($data['medico_id'], $data['fecha'], $data['hora'], $data['servicio_id'])) {
            return back()->withInput()->with('error', 'El horario está ocupado. No hay disponibilidad para esa hora.');
        }

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

        // Verificar disponibilidad del horario (excluir la cita actual)
        if (!$this->isHorarioDisponible($data['medico_id'], $data['fecha'], $data['hora'], $data['servicio_id'], $cita->id)) {
            return back()->withInput()->with('error', 'El horario está ocupado. No hay disponibilidad para esa hora.');
        }

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
            ->with(['paciente.portafolio', 'medico.usuario', 'estado', 'servicio', 'modalidad', 'pagos'])
            ->orderBy('hora')
            ->get();

        // Enriquecer con precio sugerido y estado de pago
        $citas->transform(function ($cita) use ($empresaId) {
            $precioSugerido = null;
            if ($cita->servicio_id && $cita->paciente?->portafolio_id) {
                $precio = \App\Models\PrecioServicio::where('empresa_id', $empresaId)
                    ->where('servicio_id', $cita->servicio_id)
                    ->where('portafolio_id', $cita->paciente->portafolio_id)
                    ->value('precio');
                $precioSugerido = $precio;
            }

            $pagoPagado = $cita->pagos->where('estado', 'pagado')->first();

            $data = $cita->toArray();
            $data['precio_sugerido'] = $precioSugerido;
            $data['pago_estado'] = $pagoPagado ? 'pagado' : 'pendiente';
            return $data;
        });

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
