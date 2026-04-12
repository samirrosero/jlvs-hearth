<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\EjecucionCita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Valoracion;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function __invoke()
    {
        $empresaId = auth()->user()->empresa_id;
        $hoy       = now();

        $totalPacientes = Paciente::where('empresa_id', $empresaId)->count();
        $totalMedicos   = Medico::where('empresa_id', $empresaId)->count();
        $totalCitas     = Cita::where('empresa_id', $empresaId)->count();
        $citasMes       = Cita::where('empresa_id', $empresaId)
            ->whereYear('fecha', $hoy->year)
            ->whereMonth('fecha', $hoy->month)
            ->count();

        $citasPorEstado = Cita::where('empresa_id', $empresaId)
            ->join('estados_cita', 'citas.estado_id', '=', 'estados_cita.id')
            ->select('estados_cita.nombre as estado', 'estados_cita.color_hex as color', DB::raw('count(*) as total'))
            ->groupBy('estados_cita.nombre', 'estados_cita.color_hex')
            ->get();

        $citasPorMes = Cita::where('empresa_id', $empresaId)
            ->where('fecha', '>=', now()->subMonths(6)->startOfMonth())
            ->select(DB::raw("DATE_FORMAT(fecha, '%Y-%m') as mes"), DB::raw('count(*) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $medicoTop = Medico::where('medicos.empresa_id', $empresaId)
            ->join('citas', 'medicos.id', '=', 'citas.medico_id')
            ->join('users', 'medicos.usuario_id', '=', 'users.id')
            ->select('users.nombre as medico', 'medicos.especialidad', DB::raw('count(citas.id) as total_citas'))
            ->groupBy('medicos.id', 'users.nombre', 'medicos.especialidad')
            ->orderByDesc('total_citas')
            ->limit(5)
            ->get();

        $duracionPromedio = EjecucionCita::whereHas('cita', fn ($q) => $q->where('empresa_id', $empresaId))
            ->whereNotNull('duracion_minutos')
            ->avg('duracion_minutos');

        $promedioValoraciones = Valoracion::whereHas('cita', fn ($q) => $q->where('empresa_id', $empresaId))
            ->avg('puntuacion');
        $totalValoraciones = Valoracion::whereHas('cita', fn ($q) => $q->where('empresa_id', $empresaId))
            ->count();

        $proximasCitas = Cita::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->where('fecha', '>=', $hoy->toDateString())
            ->with('paciente', 'medico.usuario', 'estado')
            ->orderBy('fecha')->orderBy('hora')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalPacientes', 'totalMedicos', 'totalCitas', 'citasMes',
            'citasPorEstado', 'citasPorMes', 'medicoTop',
            'duracionPromedio', 'promedioValoraciones', 'totalValoraciones',
            'proximasCitas'
        ));
    }
}
