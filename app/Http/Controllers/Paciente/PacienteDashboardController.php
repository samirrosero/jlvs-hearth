<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\HistoriaClinica;
use App\Models\Medico;
use App\Models\OrdenMedica;
use App\Models\Servicio;
use App\Models\ModalidadCita;
use Barryvdh\DomPDF\Facade\Pdf;

class PacienteDashboardController extends Controller
{
    public function __invoke()
    {
        $paciente = auth()->user()->paciente;

        // Próximas citas del paciente
        $proximasCitas = Cita::where('paciente_id', $paciente->id)
            ->where('activo', true)
            ->where('fecha', '>=', now()->toDateString())
            ->with('medico.usuario', 'estado', 'servicio')
            ->orderBy('fecha')->orderBy('hora')
            ->limit(5)
            ->get();

        // Total de citas históricas
        $totalCitas = Cita::where('paciente_id', $paciente->id)->count();

        // Total de historias clínicas
        $totalHistorias = HistoriaClinica::where('paciente_id', $paciente->id)->count();

        // Órdenes médicas pendientes de autorización
        $ordenesPendientes = OrdenMedica::where('paciente_id', $paciente->id)
            ->where('estado', 'pendiente')
            ->with('historiaClinica.ejecucionCita.cita.medico.usuario')
            ->orderByDesc('created_at')
            ->get();

        // Datos para el modal de agendamiento
        $medicos = Medico::with('usuario')->get();
        $servicios = Servicio::all();
        $modalidades = ModalidadCita::all();

        return view('paciente.dashboard', compact(
            'paciente', 'proximasCitas', 'totalCitas', 'totalHistorias',
            'ordenesPendientes', 'medicos', 'servicios', 'modalidades'
        ));
    }
    // Por ahora solo retornamos un texto para probar que la ruta funciona
public function descargarCertificado()
{
    // Cargamos el paciente con su relación de empresa
    $paciente = \App\Models\Paciente::with('empresa')
                ->where('usuario_id', auth()->id())
                ->first();

    if (!$paciente) {
        return back()->with('error', 'No se encontró información.');
    }

    $pdf = Pdf::loadView('paciente.certificado_pdf', compact('paciente'));
    
    return $pdf->stream('Certificado_' . $paciente->identificacion . '.pdf');
}

}