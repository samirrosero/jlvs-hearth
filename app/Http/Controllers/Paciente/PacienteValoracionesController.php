<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Valoracion;

class PacienteValoracionesController extends Controller
{
    public function index()
    {
        $paciente = auth()->user()->paciente;

        // Valoraciones realizadas por el paciente, ordenadas por fecha más reciente
        $valoraciones = Valoracion::where('paciente_id', $paciente->id)
            ->with('cita.medico.usuario', 'cita.servicio')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('paciente.valoraciones.index', compact('valoraciones'));
    }
}
