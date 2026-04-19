<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\HistoriaClinica;
use App\Models\Paciente;
use App\Models\SignosVitales;

class MedicoPacientesController extends Controller
{
    public function index()
    {
        $medicoId = auth()->user()->medico->id;

        $pacientes = Paciente::whereHas('citas', fn ($q) => $q->where('medico_id', $medicoId))
            ->withCount(['citas as total_consultas' => fn ($q) => $q->where('medico_id', $medicoId)])
            ->with(['citas' => fn ($q) => $q->where('medico_id', $medicoId)->latest('fecha')->limit(1)])
            ->when(request('buscar'), fn ($q) => $q->where('nombre_completo', 'like', '%' . request('buscar') . '%'))
            ->orderBy('nombre_completo')
            ->paginate(15)
            ->withQueryString();

        return view('medico.pacientes.index', compact('pacientes'));
    }

    public function show(Paciente $paciente)
    {
        $user     = auth()->user();
        $medicoId = $user->medico->id;

        // Doble verificación: el paciente es de esta empresa Y el médico lo ha atendido
        abort_unless(
            $paciente->empresa_id === $user->empresa_id &&
            Cita::where('paciente_id', $paciente->id)->where('medico_id', $medicoId)->exists(),
            403
        );

        $historias = HistoriaClinica::where('paciente_id', $paciente->id)
            ->with('ejecucionCita.cita', 'recetasMedicas')
            ->orderByDesc('created_at')
            ->get();

        $signosVitales = SignosVitales::where('paciente_id', $paciente->id)
            ->with('ejecucionCita.cita')
            ->orderByDesc('created_at')
            ->get();

        $antecedentes = $paciente->antecedentes()->orderByDesc('created_at')->get();

        return view('medico.pacientes.show', compact('paciente', 'historias', 'signosVitales', 'antecedentes'));
    }
}
