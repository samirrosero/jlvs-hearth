<?php

namespace App\Http\Controllers\GestorCitas;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\HorarioMedico;
use App\Models\Medico;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReasignarCitasMedicoController extends Controller
{
    private const ESTADOS_LIBERAN  = [4, 5]; // cancelada, no asistió
    private const ESTADO_PENDIENTE = 1;

    /**
     * Reasigna en masa todas las citas pendientes de un médico ausente
     * a otros médicos de la misma especialidad disponibles ese día.
     *
     * POST /gestor/citas/reasignar-medico
     *   { "medico_id_ausente": 5, "fecha": "2026-04-29" }
     *
     * Responde con cuántas citas se reasignaron y cuántas quedaron sin suplente.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'medico_id_ausente' => ['required', 'integer', "exists:medicos,id,empresa_id,{$empresaId}"],
            'fecha'             => ['required', 'date'],
        ]);

        $medicoAusente = Medico::findOrFail($data['medico_id_ausente']);
        $fecha         = Carbon::parse($data['fecha']);
        $diaSemana     = (int) $fecha->format('w');

        // Citas pendientes del médico ausente ese día
        $citasAfectadas = Cita::where('medico_id', $data['medico_id_ausente'])
            ->whereDate('fecha', $fecha->toDateString())
            ->where('estado_id', self::ESTADO_PENDIENTE)
            ->where('activo', true)
            ->with('servicio')
            ->orderBy('hora')
            ->get();

        if ($citasAfectadas->isEmpty()) {
            return response()->json([
                'message'      => 'No hay citas pendientes para reasignar en esa fecha.',
                'reasignadas'  => 0,
                'sin_suplente' => 0,
                'total'        => 0,
            ]);
        }

        // Médicos suplentes: misma especialidad, mismo día de semana, distinto al ausente
        $suplentes = Medico::where('empresa_id', $empresaId)
            ->where('especialidad', $medicoAusente->especialidad)
            ->where('id', '!=', $medicoAusente->id)
            ->whereHas('horarios', fn ($q) => $q->where('dia_semana', $diaSemana)->where('activo', true))
            ->get();

        if ($suplentes->isEmpty()) {
            return response()->json([
                'message'      => 'No hay médicos suplentes disponibles para la especialidad "' . $medicoAusente->especialidad . '" ese día.',
                'reasignadas'  => 0,
                'sin_suplente' => $citasAfectadas->count(),
                'total'        => $citasAfectadas->count(),
            ], 422);
        }

        $reasignadas  = 0;
        $sinSuplente  = 0;
        $detalle      = [];

        foreach ($citasAfectadas as $cita) {
            $hora     = substr($cita->hora, 0, 5);
            $duracion = $cita->servicio?->duracion_minutos ?? 30;
            $horaFin  = Carbon::parse("{$fecha->toDateString()} {$hora}")->addMinutes($duracion)->format('H:i:s');
            $asignada = false;

            // Ordenar suplentes por carga del día (menor primero) para cada cita
            $cargaPorSuplente = Cita::whereIn('medico_id', $suplentes->pluck('id'))
                ->whereDate('fecha', $fecha->toDateString())
                ->where('activo', true)
                ->selectRaw('medico_id, COUNT(*) as total')
                ->groupBy('medico_id')
                ->pluck('total', 'medico_id');

            $suplentesOrdenados = $suplentes->sortBy(fn ($s) => $cargaPorSuplente[$s->id] ?? 0);

            foreach ($suplentesOrdenados as $suplente) {
                // Verificar que el suplente trabaja en esa hora ese día
                $tieneHorario = HorarioMedico::where('medico_id', $suplente->id)
                    ->where('dia_semana', $diaSemana)
                    ->where('hora_inicio', '<=', $hora)
                    ->where('hora_fin', '>=', $horaFin)
                    ->where('activo', true)
                    ->exists();

                if (!$tieneHorario) {
                    continue;
                }

                // Verificar que no tenga otra cita que se solape
                $estaOcupado = Cita::where('medico_id', $suplente->id)
                    ->whereDate('fecha', $fecha->toDateString())
                    ->whereNotIn('estado_id', self::ESTADOS_LIBERAN)
                    ->where('activo', true)
                    ->whereRaw('hora < ?', [$horaFin])
                    ->whereRaw(
                        'ADDTIME(hora, SEC_TO_TIME(COALESCE((SELECT duracion_minutos FROM servicios WHERE id = servicio_id), 30) * 60)) > ?',
                        ["{$hora}:00"]
                    )
                    ->exists();

                if (!$estaOcupado) {
                    $cita->update(['medico_id' => $suplente->id]);
                    $reasignadas++;
                    $asignada = true;
                    $detalle[] = [
                        'cita_id'          => $cita->id,
                        'hora'             => $hora,
                        'medico_suplente'  => $suplente->usuario->nombre ?? "Médico #{$suplente->id}",
                        'estado'           => 'reasignada',
                    ];
                    break;
                }
            }

            if (!$asignada) {
                $sinSuplente++;
                $detalle[] = [
                    'cita_id' => $cita->id,
                    'hora'    => $hora,
                    'estado'  => 'sin_suplente_disponible',
                ];
            }
        }

        return response()->json([
            'message'      => "Reasignación completada: {$reasignadas} de {$citasAfectadas->count()} citas reasignadas.",
            'reasignadas'  => $reasignadas,
            'sin_suplente' => $sinSuplente,
            'total'        => $citasAfectadas->count(),
            'detalle'      => $detalle,
        ]);
    }
}
