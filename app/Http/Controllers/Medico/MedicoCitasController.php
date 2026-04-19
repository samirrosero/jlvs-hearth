<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\EstadoCita;

class MedicoCitasController extends Controller
{
    public function index()
    {
        $medico = auth()->user()->medico;

        $citas = Cita::where('medico_id', $medico->id)
            ->with('paciente', 'estado', 'servicio', 'modalidad')
            ->when(request('fecha'), fn ($q) => $q->where('fecha', request('fecha')))
            ->when(request('estado_id'), fn ($q) => $q->where('estado_id', request('estado_id')))
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate(15)
            ->withQueryString();

        $estados = EstadoCita::all();

        return view('medico.citas.index', compact('citas', 'estados'));
    }

    public function atender(Cita $cita)
    {
        abort_if($cita->medico_id !== auth()->user()->medico->id, 403);

        $cita->load([
            'paciente',
            'estado',
            'servicio',
            'modalidad',
            'ejecucion.historiaClinica.recetasMedicas',
            'ejecucion.signosVitales',
        ]);

        return view('medico.citas.atender', compact('cita'));
    }
}
