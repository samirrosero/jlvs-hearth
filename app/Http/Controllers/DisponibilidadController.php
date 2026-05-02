<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\HorarioMedico;
use App\Models\Medico;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisponibilidadController extends Controller
{
    // Estados que liberan el slot (cancelada=4, no asistió=5)
    private const ESTADOS_LIBERAN = [4, 5];

    /**
     * Devuelve qué días del mes tiene horario activo el médico.
     * GET /medicos/{medico}/dias-disponibles?mes=YYYY-MM
     */
    public function diasDisponibles(Request $request, Medico $medico): JsonResponse
    {
        $request->validate([
            'mes' => ['required', 'date_format:Y-m'],
        ]);

        // Días de la semana en los que el médico tiene horario activo
        $diasConHorario = HorarioMedico::where('medico_id', $medico->id)
            ->where('activo', true)
            ->pluck('dia_semana') // convenio: N%7 → 1=lun…6=sab, 0=dom
            ->unique()
            ->toArray();

        if (empty($diasConHorario)) {
            return response()->json(['dias_disponibles' => []]);
        }

        $inicio = Carbon::createFromFormat('Y-m', $request->input('mes'))->startOfMonth();
        $fin    = $inicio->copy()->endOfMonth();
        $hoy    = now()->startOfDay();

        $diasDisponibles = [];
        $cursor = $inicio->copy();

        while ($cursor->lte($fin)) {
            // No ofrecer días pasados
            if ($cursor->gte($hoy)) {
                $diaSemana = (int) $cursor->format('N') % 7;
                if (in_array($diaSemana, $diasConHorario)) {
                    $diasDisponibles[] = $cursor->toDateString();
                }
            }
            $cursor->addDay();
        }

        return response()->json([
            'medico_id'        => $medico->id,
            'mes'              => $request->input('mes'),
            'dias_disponibles' => $diasDisponibles,
        ]);
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'medico_id'   => ['required', 'integer', 'exists:medicos,id'],
            'fecha'       => ['required', 'date', 'after_or_equal:today'],
            'servicio_id' => ['nullable', 'integer', 'exists:servicios,id'],
        ]);

        $medicoId   = (int) $request->input('medico_id');
        $fecha      = Carbon::parse($request->input('fecha'));
        $diaSemana  = (int) $fecha->format('N') % 7; // Carbon N: 1=lun..7=dom → 1-6,0

        $servicio         = $request->filled('servicio_id')
            ? Servicio::find($request->input('servicio_id'))
            : null;
        $duracionMinutos  = $servicio?->duracion_minutos ?? 30;

        // Horarios del médico para ese día de la semana
        $horarios = HorarioMedico::where('medico_id', $medicoId)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->get();

        if ($horarios->isEmpty()) {
            return response()->json([
                'disponible' => false,
                'slots'      => [],
                'mensaje'    => 'El médico no tiene horario disponible ese día.',
            ]);
        }

        // Citas ya ocupadas ese día (excluir canceladas y no asistió)
        $horasOcupadas = Cita::where('medico_id', $medicoId)
            ->whereDate('fecha', $fecha->toDateString())
            ->whereNotIn('estado_id', self::ESTADOS_LIBERAN)
            ->where('activo', true)
            ->pluck('hora')
            ->map(fn ($h) => substr($h, 0, 5)) // HH:MM
            ->toArray();

        // Generar todos los slots posibles según los bloques horarios
        $slots = [];
        foreach ($horarios as $horario) {
            $cursor = Carbon::parse($fecha->toDateString() . ' ' . $horario->hora_inicio);
            $fin    = Carbon::parse($fecha->toDateString() . ' ' . $horario->hora_fin);

            while ($cursor->copy()->addMinutes($duracionMinutos)->lte($fin)) {
                $hora = $cursor->format('H:i');
                if (!in_array($hora, $horasOcupadas)) {
                    $slots[] = $hora;
                }
                $cursor->addMinutes($duracionMinutos);
            }
        }

        if (count($slots) === 0) {
            return response()->json([
                'disponible'       => false,
                'slots'            => [],
                'fecha'            => $fecha->toDateString(),
                'duracion_minutos' => $duracionMinutos,
                'servicio'         => $servicio?->nombre,
                'mensaje'          => 'El horario está ocupado. No hay disponibilidad para ese día.',
            ]);
        }

        return response()->json([
            'disponible'       => true,
            'slots'            => $slots,
            'fecha'            => $fecha->toDateString(),
            'duracion_minutos' => $duracionMinutos,
            'servicio'         => $servicio?->nombre,
        ]);
    }
}
