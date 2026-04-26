<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\HorarioMedico;
use App\Models\Medico;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisponibilidadEspecialidadController extends Controller
{
    private const ESTADOS_LIBERAN = [4, 5]; // cancelada, no asistió

    /**
     * Devuelve todos los slots disponibles para una especialidad y fecha,
     * sin exponer a qué médico pertenece cada slot.
     *
     * GET /citas/disponibilidad-por-especialidad
     *   ?especialidad=Medicina General
     *   &fecha=2026-04-29
     *   &servicio_id=1   (opcional, para respetar duracion_minutos)
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'especialidad' => ['required', 'string', 'max:100'],
            'fecha'        => ['required', 'date', 'after_or_equal:today'],
            'servicio_id'  => ['nullable', 'integer', 'exists:servicios,id'],
        ]);

        $empresaId    = auth()->user()->empresa_id;
        $especialidad = $request->input('especialidad');
        $fecha        = Carbon::parse($request->input('fecha'));
        $diaSemana    = (int) $fecha->format('w'); // 0=dom … 6=sáb

        $servicio = $request->filled('servicio_id')
            ? Servicio::find($request->input('servicio_id'))
            : null;
        $duracion = $servicio?->duracion_minutos ?? 30;

        // Médicos de esa especialidad en la empresa
        $medicosIds = Medico::where('empresa_id', $empresaId)
            ->where('especialidad', 'like', "%{$especialidad}%")
            ->pluck('id');

        if ($medicosIds->isEmpty()) {
            return response()->json([
                'disponible' => false,
                'slots'      => [],
                'mensaje'    => 'No hay médicos registrados para esa especialidad.',
            ]);
        }

        // Horarios activos de esos médicos para ese día de la semana
        $horariosPorMedico = HorarioMedico::whereIn('medico_id', $medicosIds)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->get()
            ->groupBy('medico_id');

        if ($horariosPorMedico->isEmpty()) {
            return response()->json([
                'disponible' => false,
                'slots'      => [],
                'mensaje'    => 'No hay disponibilidad para esa especialidad en la fecha seleccionada.',
            ]);
        }

        // Citas ya ocupadas ese día para cada médico disponible
        $citasOcupadas = Cita::whereIn('medico_id', $horariosPorMedico->keys())
            ->whereDate('fecha', $fecha->toDateString())
            ->whereNotIn('estado_id', self::ESTADOS_LIBERAN)
            ->where('activo', true)
            ->get(['medico_id', 'hora'])
            ->groupBy('medico_id')
            ->map(fn ($citas) => $citas->pluck('hora')
                ->map(fn ($h) => substr($h, 0, 5))
                ->toArray()
            );

        // Generar la unión de slots libres de todos los médicos
        // Si al menos UN médico tiene libre un slot, ese slot aparece
        $slotsDisponibles = collect();

        foreach ($horariosPorMedico as $medicoId => $horarios) {
            $ocupadas = $citasOcupadas[$medicoId] ?? [];

            foreach ($horarios as $horario) {
                $cursor = Carbon::parse($fecha->toDateString() . ' ' . $horario->hora_inicio);
                $fin    = Carbon::parse($fecha->toDateString() . ' ' . $horario->hora_fin);

                while ($cursor->copy()->addMinutes($duracion)->lte($fin)) {
                    $hora = $cursor->format('H:i');
                    if (!in_array($hora, $ocupadas)) {
                        $slotsDisponibles->push($hora);
                    }
                    $cursor->addMinutes($duracion);
                }
            }
        }

        $slots = $slotsDisponibles->unique()->sort()->values();

        return response()->json([
            'disponible'       => $slots->isNotEmpty(),
            'slots'            => $slots,
            'fecha'            => $fecha->toDateString(),
            'especialidad'     => $especialidad,
            'duracion_minutos' => $duracion,
        ]);
    }
}
