<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Mail\CitaAgendadaMail;
use App\Models\Cita;
use App\Models\HorarioMedico;
use App\Models\Medico;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AgendarCitaPacienteController extends Controller
{
    private const ESTADOS_LIBERAN  = [4, 5]; // cancelada, no asistió
    private const ESTADO_PENDIENTE = 1;

    /**
     * El paciente agenda una cita eligiendo especialidad + fecha + hora.
     * El backend asigna internamente el médico con menor carga ese día.
     * El nombre del médico NO se devuelve al paciente.
     *
     * POST /paciente/citas/agendar
     */
    public function __invoke(Request $request): JsonResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'especialidad'  => ['required', 'string', 'max:100'],
            'fecha'         => ['required', 'date', 'after_or_equal:tomorrow'],
            'hora'          => ['required', 'date_format:H:i'],
            'modalidad_id'  => ['required', 'exists:modalidades_cita,id'],
            'portafolio_id' => ['nullable', 'integer', "exists:portafolios,id,empresa_id,{$empresaId}"],
            'servicio_id'   => ['nullable', 'integer', "exists:servicios,id,empresa_id,{$empresaId}"],
        ]);

        $fecha     = Carbon::parse($data['fecha']);
        $hora      = $data['hora'];
        $diaSemana = (int) $fecha->format('w'); // 0=dom … 6=sáb

        $servicio = isset($data['servicio_id']) ? Servicio::find($data['servicio_id']) : null;
        $duracion = $servicio?->duracion_minutos ?? 30;
        $horaFin  = Carbon::parse("{$data['fecha']} {$hora}")->addMinutes($duracion)->format('H:i:s');

        // 1. Médicos de esa especialidad con horario activo ese día que cubran ese slot
        $medicosIds = Medico::where('empresa_id', $empresaId)
            ->where('especialidad', 'like', "%{$data['especialidad']}%")
            ->pluck('id');

        if ($medicosIds->isEmpty()) {
            return response()->json(['message' => 'No hay médicos registrados para esa especialidad.'], 422);
        }

        $medicosConHorario = HorarioMedico::whereIn('medico_id', $medicosIds)
            ->where('dia_semana', $diaSemana)
            ->where('hora_inicio', '<=', $hora)
            ->where('hora_fin', '>=', $horaFin)
            ->where('activo', true)
            ->pluck('medico_id')
            ->unique();

        if ($medicosConHorario->isEmpty()) {
            return response()->json(['message' => 'No hay médicos disponibles para esa especialidad en el horario indicado.'], 422);
        }

        // 2. Excluir los que ya tienen una cita que se solapa en ese slot
        $medicosOcupados = Cita::whereIn('medico_id', $medicosConHorario)
            ->whereDate('fecha', $fecha->toDateString())
            ->whereNotIn('estado_id', self::ESTADOS_LIBERAN)
            ->where('activo', true)
            ->whereRaw('hora < ?', [$horaFin])
            ->whereRaw(
                'ADDTIME(hora, SEC_TO_TIME(COALESCE((SELECT duracion_minutos FROM servicios WHERE id = servicio_id), 30) * 60)) > ?',
                ["{$hora}:00"]
            )
            ->pluck('medico_id')
            ->unique();

        $medicosLibres = $medicosConHorario->diff($medicosOcupados);

        if ($medicosLibres->isEmpty()) {
            return response()->json(['message' => 'No hay disponibilidad en ese horario. Por favor elige otra hora.'], 422);
        }

        // 3. Balance de carga: asignar al médico con menos citas ese día
        $conCitas = Cita::whereIn('medico_id', $medicosLibres)
            ->whereDate('fecha', $fecha->toDateString())
            ->where('activo', true)
            ->selectRaw('medico_id, COUNT(*) as total')
            ->groupBy('medico_id')
            ->orderBy('total')
            ->pluck('total', 'medico_id');

        // Médicos sin ninguna cita ese día tienen prioridad máxima (total = 0)
        $medicoId = $medicosLibres
            ->sortBy(fn ($id) => $conCitas[$id] ?? 0)
            ->first();

        // 4. Crear la cita
        $cita = Cita::create([
            'empresa_id'    => $empresaId,
            'medico_id'     => $medicoId,
            'paciente_id'   => auth()->user()->paciente->id,
            'estado_id'     => self::ESTADO_PENDIENTE,
            'modalidad_id'  => $data['modalidad_id'],
            'portafolio_id' => $data['portafolio_id'] ?? null,
            'servicio_id'   => $data['servicio_id'] ?? null,
            'fecha'         => $data['fecha'],
            'hora'          => $hora,
        ]);

        // Correo de confirmación (sin exponer el médico asignado)
        $correo = auth()->user()->email ?? $cita->paciente?->correo;
        if ($correo) {
            Mail::to($correo)->queue(new CitaAgendadaMail($cita->load('medico.usuario', 'paciente', 'estado', 'modalidad', 'servicio', 'empresa')));
        }

        return response()->json([
            'message'          => 'Cita agendada con éxito.',
            'cita_id'          => $cita->id,
            'fecha'            => $fecha->toDateString(),
            'hora'             => $hora,
            'especialidad'     => $data['especialidad'],
            'servicio'         => $servicio?->nombre,
            'duracion_minutos' => $duracion,
        ], 201);
    }
}
