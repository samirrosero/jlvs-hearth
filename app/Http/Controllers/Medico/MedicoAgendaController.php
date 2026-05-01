<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicoAgendaController extends Controller
{
    public function index(Request $request): View
    {
        $medico = auth()->user()->medico;

        // Semana a mostrar (por defecto la actual, navegable por ?semana=YYYY-MM-DD)
        $inicio = $request->filled('semana')
            ? Carbon::parse($request->semana)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $fin = $inicio->copy()->endOfWeek(Carbon::SUNDAY);

        $citas = Cita::where('medico_id', $medico->id)
            ->where('activo', true)
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->with('paciente', 'estado', 'modalidad', 'servicio')
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get()
            ->groupBy(fn ($c) => $c->fecha->toDateString());

        // Generar los 7 días de la semana
        $dias = collect();
        for ($i = 0; $i < 7; $i++) {
            $dias->push($inicio->copy()->addDays($i));
        }

        return view('medico.agenda.index', [
            'citas'     => $citas,
            'dias'      => $dias,
            'inicio'    => $inicio,
            'fin'       => $fin,
            'semanaAnt' => $inicio->copy()->subWeek()->toDateString(),
            'semanaSig' => $inicio->copy()->addWeek()->toDateString(),
            'hoy'       => Carbon::today()->toDateString(),
        ]);
    }
}
