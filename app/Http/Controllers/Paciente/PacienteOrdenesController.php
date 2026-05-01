<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\OrdenMedica;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PacienteOrdenesController extends Controller
{
    public function index(): View
    {
        $paciente = auth()->user()->paciente;

        $ordenes = OrdenMedica::where('paciente_id', $paciente->id)
            ->with([
                'historiaClinica.ejecucionCita.cita.medico.usuario',
                'historiaClinica.ejecucionCita.cita.servicio',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('estado');

        $pendientes  = $ordenes->get('pendiente',  collect());
        $autorizadas = $ordenes->get('autorizada',  collect());
        $completadas = $ordenes->get('completada',  collect());
        $canceladas  = $ordenes->get('cancelada',   collect());

        return view('paciente.ordenes.index', compact(
            'pendientes', 'autorizadas', 'completadas', 'canceladas'
        ));
    }

    public function autorizar(Request $request, OrdenMedica $ordenMedica): RedirectResponse
    {
        // Solo el paciente dueño puede autorizar
        abort_unless($ordenMedica->paciente_id === auth()->user()->paciente?->id, 403);
        abort_unless($ordenMedica->estado === 'pendiente', 422);

        $request->validate([
            'autorizado_via' => 'required|in:virtual,presencial',
            'nota_paciente'  => 'nullable|string|max:300',
        ]);

        $ordenMedica->update([
            'estado'         => 'autorizada',
            'autorizado_via' => $request->autorizado_via,
            'autorizado_en'  => now(),
        ]);

        $via = $request->autorizado_via === 'virtual' ? 'en línea' : 'de forma presencial';

        return back()->with('exito', "Orden autorizada {$via} correctamente.");
    }
}
