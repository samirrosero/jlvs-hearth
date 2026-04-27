<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Medico;

class GestorDashboardController extends Controller
{
    public function __invoke()
    {
        $empresaId = auth()->user()->empresa_id;
        $hoy       = now()->toDateString();

        $citasHoy       = Cita::where('empresa_id', $empresaId)->where('fecha', $hoy)->count();
        $citasPendientes = Cita::where('empresa_id', $empresaId)
            ->whereHas('estado', fn ($q) => $q->where('nombre', 'like', '%pendiente%'))
            ->count();
        $totalPacientes = Paciente::where('empresa_id', $empresaId)->count();
        $totalMedicos   = Medico::where('empresa_id', $empresaId)->count();

        $proximasCitas = Cita::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->where('fecha', '>=', $hoy)
            ->with('paciente', 'medico.usuario', 'estado', 'servicio')
            ->orderBy('fecha')->orderBy('hora')
            ->limit(8)
            ->get();

        return view('gestor.dashboard', compact(
            'citasHoy', 'citasPendientes', 'totalPacientes', 'totalMedicos', 'proximasCitas'
        ));
    }
}