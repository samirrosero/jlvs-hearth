<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Mail\HistoriaClinicaMail;
use App\Models\HistoriaClinica;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

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
        abort_if($historia->paciente_id !== auth()->user()->paciente->id, 403);

        $historia->load(
            'ejecucionCita.cita.medico.usuario',
            'ejecucionCita.signosVitales',
            'recetasMedicas',
        );

        return view('paciente.historial.show', compact('historia'));
    }

    public function pdf(HistoriaClinica $historia): Response
    {
        abort_if($historia->paciente_id !== auth()->user()->paciente->id, 403);

        $historia->load(
            'paciente.empresa',
            'ejecucionCita.cita.medico.usuario',
            'ejecucionCita.cita.modalidad',
            'ejecucionCita.cita.portafolio',
            'ejecucionCita.signosVitales',
            'recetasMedicas',
        );

        $paciente      = $historia->paciente;
        $empresa       = $paciente->empresa;
        $signosVitales = $historia->ejecucionCita?->signosVitales;

        $pdf = Pdf::loadView('pdf.historia_clinica', compact(
            'historia',
            'paciente',
            'empresa',
            'signosVitales',
        ))->setPaper('letter', 'portrait');

        $nombreArchivo = 'historia-clinica-' . str_pad($historia->id, 8, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    public function enviarCorreo(HistoriaClinica $historia): RedirectResponse
    {
        abort_if($historia->paciente_id !== auth()->user()->paciente->id, 403);

        $historia->load(
            'paciente.empresa',
            'ejecucionCita.cita.medico.usuario',
            'ejecucionCita.cita.modalidad',
            'ejecucionCita.cita.portafolio',
            'ejecucionCita.signosVitales',
            'recetasMedicas',
        );

        $paciente = $historia->paciente;
        $correo   = $paciente->correo ?? auth()->user()->email;

        abort_if(! $correo, 422, 'No hay correo registrado para este paciente.');

        Mail::to($correo)->send(new HistoriaClinicaMail($historia));

        return back()->with('success', 'Historia clínica enviada a ' . $correo);
    }
}
