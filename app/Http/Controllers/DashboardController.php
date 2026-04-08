<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\EjecucionCita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Valoracion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $empresaId = auth()->user()->empresa_id;
        $hoy       = now();
        $mesActual = $hoy->format('Y-m');

        // ── Totales generales ──────────────────────────────────────────
        $totalPacientes = Paciente::where('empresa_id', $empresaId)->count();
        $totalMedicos   = Medico::where('empresa_id', $empresaId)->count();

        $totalCitas = Cita::where('empresa_id', $empresaId)->count();
        $citasMes   = Cita::where('empresa_id', $empresaId)
            ->whereYear('fecha', $hoy->year)
            ->whereMonth('fecha', $hoy->month)
            ->count();

        // ── Citas por estado ───────────────────────────────────────────
        $citasPorEstado = Cita::where('empresa_id', $empresaId)
            ->join('estados_cita', 'citas.estado_id', '=', 'estados_cita.id')
            ->select('estados_cita.nombre as estado', DB::raw('count(*) as total'))
            ->groupBy('estados_cita.nombre')
            ->get();

        // ── Citas por mes (últimos 6 meses) ────────────────────────────
        $citasPorMes = Cita::where('empresa_id', $empresaId)
            ->where('fecha', '>=', now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(fecha, '%Y-%m') as mes"),
                DB::raw('count(*) as total')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // ── Ejecuciones / atenciones reales ────────────────────────────
        $totalEjecuciones = EjecucionCita::whereHas('cita', fn ($q) => $q->where('empresa_id', $empresaId))->count();

        $ejecucionesMes = EjecucionCita::whereHas('cita', fn ($q) => $q->where('empresa_id', $empresaId))
            ->whereYear('inicio_atencion', $hoy->year)
            ->whereMonth('inicio_atencion', $hoy->month)
            ->count();

        // ── Duración promedio de consultas ─────────────────────────────
        $duracionPromedio = EjecucionCita::whereHas('cita', fn ($q) => $q->where('empresa_id', $empresaId))
            ->whereNotNull('duracion_minutos')
            ->avg('duracion_minutos');

        // ── Especialidad con más citas atendidas ───────────────────────
        $especialidadTop = Medico::where('medicos.empresa_id', $empresaId)
            ->join('citas', 'medicos.id', '=', 'citas.medico_id')
            ->select('medicos.especialidad', DB::raw('count(citas.id) as total_citas'))
            ->groupBy('medicos.especialidad')
            ->orderByDesc('total_citas')
            ->limit(5)
            ->get();

        // ── Médico con más citas ────────────────────────────────────────
        $medicoTop = Medico::where('medicos.empresa_id', $empresaId)
            ->join('citas', 'medicos.id', '=', 'citas.medico_id')
            ->join('users', 'medicos.usuario_id', '=', 'users.id')
            ->select(
                'users.nombre as medico',
                'medicos.especialidad',
                DB::raw('count(citas.id) as total_citas')
            )
            ->groupBy('medicos.id', 'users.nombre', 'medicos.especialidad')
            ->orderByDesc('total_citas')
            ->limit(5)
            ->get();

        // ── Pacientes nuevos por mes (últimos 6 meses) ─────────────────
        $pacientesPorMes = Paciente::where('empresa_id', $empresaId)
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as mes"),
                DB::raw('count(*) as total')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // ── Valoraciones ───────────────────────────────────────────────
        $promedioValoraciones = Valoracion::whereHas('cita', fn ($q) => $q->where('empresa_id', $empresaId))
            ->avg('puntuacion');

        $totalValoraciones = Valoracion::whereHas('cita', fn ($q) => $q->where('empresa_id', $empresaId))
            ->count();

        // ── Próximas citas (hoy y mañana) ──────────────────────────────
        $proximasCitas = Cita::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->whereBetween('fecha', [$hoy->toDateString(), $hoy->copy()->addDay()->toDateString()])
            ->with('paciente', 'medico.usuario', 'estado')
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();

        return response()->json([
            'totales' => [
                'pacientes'   => $totalPacientes,
                'medicos'     => $totalMedicos,
                'citas'       => $totalCitas,
                'citas_mes'   => $citasMes,
                'ejecuciones' => $totalEjecuciones,
                'ejecuciones_mes' => $ejecucionesMes,
            ],
            'duracion_promedio_minutos' => round($duracionPromedio ?? 0, 1),
            'citas_por_estado'          => $citasPorEstado,
            'citas_por_mes'             => $citasPorMes,
            'pacientes_por_mes'         => $pacientesPorMes,
            'especialidades_top'        => $especialidadTop,
            'medicos_top'               => $medicoTop,
            'valoraciones' => [
                'total'   => $totalValoraciones,
                'promedio' => round($promedioValoraciones ?? 0, 2),
            ],
            'proximas_citas'            => $proximasCitas,
        ]);
    }
}
