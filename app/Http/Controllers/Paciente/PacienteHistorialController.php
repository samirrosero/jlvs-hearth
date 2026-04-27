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
            ->with('ejecucionCita.cita.medico.usuario')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('paciente.historial.index', compact('historias'));
    }

    public function show(HistoriaClinica $historia)
    {
        $this->autorizarPaciente($historia);

        $historia->load(
            'ejecucionCita.cita.medico.usuario',
            'ejecucionCita.signosVitales',
            'recetasMedicas',
        );

        return view('paciente.historial.show', compact('historia'));
    }

    public function pdf(HistoriaClinica $historia): Response
    {
        $this->autorizarPaciente($historia);

        $this->cargarRelacionesPdf($historia);

        $pdf = Pdf::loadView('pdf.historia_clinica', [
            'historia'      => $historia,
            'paciente'      => $historia->paciente,
            'empresa'       => $historia->paciente->empresa,
            'signosVitales' => $historia->ejecucionCita?->signosVitales,
        ])->setPaper('letter', 'portrait');

        return $pdf->download($this->nombreArchivoPdf($historia->id));
    }

    public function enviarCorreo(HistoriaClinica $historia): RedirectResponse
    {
        $this->autorizarPaciente($historia);

        $this->cargarRelacionesPdf($historia);

        $correo = $historia->paciente->correo ?? auth()->user()->email;

        abort_if(! $correo, 422, 'No hay correo registrado para este paciente.');

        Mail::to($correo)->send(new HistoriaClinicaMail($historia));

        return back()->with('success', 'Historia clínica enviada a ' . $correo);
    }

    private function autorizarPaciente(HistoriaClinica $historia): void
    {
        abort_if($historia->paciente_id !== auth()->user()->paciente->id, 403);
    }

    private function cargarRelacionesPdf(HistoriaClinica $historia): void
    {
        $historia->load(
            'paciente.empresa',
            'ejecucionCita.cita.medico.usuario',
            'ejecucionCita.cita.modalidad',
            'ejecucionCita.cita.portafolio',
            'ejecucionCita.signosVitales',
            'recetasMedicas',
        );
    }

    private function nombreArchivoPdf(int $id): string
    {
        return 'historia-clinica-' . str_pad($id, 8, '0', STR_PAD_LEFT) . '.pdf';
    }
}
