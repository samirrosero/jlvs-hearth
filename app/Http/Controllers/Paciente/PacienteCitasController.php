<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\EstadoCita;

class PacienteCitasController extends Controller
{
    public function index()
    {
        $pacienteId = auth()->user()->paciente->id;

        $citas = Cita::where('paciente_id', $pacienteId)
            ->with('medico.usuario', 'estado', 'servicio', 'modalidad')
            ->when(request('estado_id'), fn ($q) => $q->where('estado_id', request('estado_id')))
            ->orderByDesc('fecha')
            ->paginate(10)
            ->withQueryString();

        $estados = EstadoCita::all();

        return view('paciente.citas.index', compact('citas', 'estados'));
    }
}
