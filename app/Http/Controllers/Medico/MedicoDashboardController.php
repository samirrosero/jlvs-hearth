<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Valoracion;
use Illuminate\Support\Facades\DB;

class MedicoDashboardController extends Controller
{
    public function __invoke()
    {
        $medico   = auth()->user()->medico;
        $medicoId = $medico->id;
        $hoy      = now();

        $citasHoy = Cita::where('medico_id', $medicoId)
            ->where('fecha', $hoy->toDateString())
            ->count();

        $citasMes = Cita::where('medico_id', $medicoId)
            ->whereYear('fecha', $hoy->year)
            ->whereMonth('fecha', $hoy->month)
            ->count();

        $totalPacientes = Cita::where('medico_id', $medicoId)
            ->distinct('paciente_id')
            ->count('paciente_id');

        $citasPendientes = Cita::where('medico_id', $medicoId)
            ->whereHas('estado', fn ($q) => $q->where('nombre', 'like', '%pendiente%'))
            ->count();

        $citasPorEstado = Cita::where('medico_id', $medicoId)
            ->join('estados_cita', 'citas.estado_id', '=', 'estados_cita.id')
            ->select('estados_cita.nombre as estado', 'estados_cita.color_hex as color', DB::raw('count(*) as total'))
            ->groupBy('estados_cita.nombre', 'estados_cita.color_hex')
            ->get();

        $citasPorMes = Cita::where('medico_id', $medicoId)
            ->where('fecha', '>=', now()->subMonths(6)->startOfMonth())
            ->select(DB::raw("DATE_FORMAT(fecha, '%Y-%m') as mes"), DB::raw('count(*) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $promedioValoraciones = Valoracion::whereHas('cita', fn ($q) => $q->where('medico_id', $medicoId))
            ->avg('puntuacion');
        $totalValoraciones = Valoracion::whereHas('cita', fn ($q) => $q->where('medico_id', $medicoId))
            ->count();

        $proximasCitas = Cita::where('medico_id', $medicoId)
            ->where('activo', true)
            ->where(fn ($q) => $q
                ->where('fecha', '>', $hoy->toDateString())
                ->orWhere(fn ($q2) => $q2
                    ->where('fecha', $hoy->toDateString())
                    ->where('hora', '>=', $hoy->toTimeString())
                )
            )
            ->with('paciente', 'estado', 'servicio')
            ->orderBy('fecha')->orderBy('hora')
            ->limit(8)
            ->get();

        return view('medico.dashboard', compact(
            'medico', 'citasHoy', 'citasMes', 'totalPacientes', 'citasPendientes',
            'citasPorEstado', 'citasPorMes',
            'promedioValoraciones', 'totalValoraciones',
            'proximasCitas'
        ));
    }
}
