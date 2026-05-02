<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Medico;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GestorDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $hoy       = now()->toDateString();

        // ── Stats ──────────────────────────────────────────────────────
        $citasHoy = Cita::where('empresa_id', $empresaId)
            ->where('fecha', $hoy)
            ->count();

        $citasPendientes = Cita::where('empresa_id', $empresaId)
            ->whereHas('estado', fn($q) => $q->where('nombre', 'like', '%pendiente%'))
            ->count();

        $totalPacientes = Paciente::where('empresa_id', $empresaId)->count();
        $totalMedicos   = Medico::where('empresa_id', $empresaId)->count();

        // ── Semana (lunes → domingo) ────────────────────────────────────
        $semanaParam  = $request->input('semana');
        $inicioSemana = $semanaParam
            ? Carbon::parse($semanaParam)->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);
        $finSemana = $inicioSemana->copy()->endOfWeek(Carbon::SUNDAY);

        // ── Citas de la semana agrupadas por fecha ──────────────────────
        $citasPorDia = Cita::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->whereBetween('fecha', [$inicioSemana->toDateString(), $finSemana->toDateString()])
            ->with('paciente', 'medico.usuario', 'estado', 'servicio', 'modalidad')
            ->orderBy('hora')
            ->get()
            ->groupBy(fn($c) => $c->fecha->toDateString());

        $diasNombres = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

        $diasSemana = collect(range(0, 6))->map(function ($i) use ($inicioSemana, $citasPorDia, $diasNombres, $hoy) {
            $dia = $inicioSemana->copy()->addDays($i);
            return [
                'fecha'  => $dia->toDateString(),
                'nombre' => $diasNombres[$i],
                'numero' => $dia->day,
                'mes'    => $dia->format('M'),
                'es_hoy' => $dia->toDateString() === $hoy,
                'citas'  => $citasPorDia->get($dia->toDateString(), collect()),
            ];
        });

        $semanaPrev  = $inicioSemana->copy()->subWeek()->toDateString();
        $semanaNext  = $inicioSemana->copy()->addWeek()->toDateString();
        $semanaLabel = $inicioSemana->locale('es')->isoFormat('D MMM')
                     . ' — '
                     . $finSemana->locale('es')->isoFormat('D MMM YYYY');

        return view('gestor.dashboard', compact(
            'citasHoy', 'citasPendientes', 'totalPacientes', 'totalMedicos',
            'diasSemana', 'semanaPrev', 'semanaNext', 'semanaLabel', 'hoy'
        ));
    }
}
