<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\OrdenMedica;
use Illuminate\View\View;

class MedicoOrdenesController extends Controller
{
    public function index(): View
    {
        $medico = auth()->user()->medico;

        $ordenes = OrdenMedica::whereHas('historiaClinica.ejecucionCita.cita', function ($q) use ($medico) {
                $q->where('medico_id', $medico->id);
            })
            ->with([
                'paciente',
                'historiaClinica.ejecucionCita.cita.servicio',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('estado');

        $pendientes  = $ordenes->get('pendiente',  collect());
        $autorizadas = $ordenes->get('autorizada',  collect());
        $completadas = $ordenes->get('completada',  collect());
        $canceladas  = $ordenes->get('cancelada',   collect());

        return view('medico.ordenes.index', compact(
            'pendientes', 'autorizadas', 'completadas', 'canceladas'
        ));
    }
}
