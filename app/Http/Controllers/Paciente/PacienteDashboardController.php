<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\HistoriaClinica;

class PacienteDashboardController extends Controller
{
    public function __invoke()
    {
        $paciente = auth()->user()->paciente;

        // Próximas citas del paciente
        $proximasCitas = Cita::where('paciente_id', $paciente->id)
            ->where('activo', true)
            ->where('fecha', '>=', now()->toDateString())
            ->with('medico.usuario', 'estado', 'servicio')
            ->orderBy('fecha')->orderBy('hora')
            ->limit(5)
            ->get();

        // Total de citas históricas
        $totalCitas = Cita::where('paciente_id', $paciente->id)->count();

        // Total de historias clínicas
        $totalHistorias = HistoriaClinica::where('paciente_id', $paciente->id)->count();

        return view('paciente.dashboard', compact(
            'paciente', 'proximasCitas', 'totalCitas', 'totalHistorias'
        ));
    }
}