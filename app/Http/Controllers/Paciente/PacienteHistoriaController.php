<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\HistoriaClinica;

class PacienteHistorialController extends Controller
{
    public function index()
    {
        $pacienteId = auth()->user()->paciente->id;

        $historias = HistoriaClinica::where('paciente_id', $pacienteId)
            ->with('ejecucionCita.cita.medico.usuario', 'recetasMedicas')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('paciente.historial.index', compact('historias'));
    }

    public function show(HistoriaClinica $historia)
    {
        // Seguridad: solo el dueño puede ver su historia
        abort_if($historia->paciente_id !== auth()->user()->paciente->id, 403);

        $historia->load('ejecucionCita.cita.medico.usuario', 'recetasMedicas');

        return view('paciente.historial.show', compact('historia'));
    }
}
