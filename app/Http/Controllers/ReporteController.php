<?php

namespace App\Http\Controllers;

use App\Exports\CitasExport;
use App\Exports\MedicosExport;
use App\Exports\PacientesExport;
use App\Models\Cita;
use App\Models\Empresa;
use App\Models\Paciente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReporteController extends Controller
{
    // ── Citas ─────────────────────────────────────────────────────────

    public function citasPdf(Request $request): Response
    {
        $empresaId = auth()->user()->empresa_id;
        $filtros   = $request->only(['fecha_desde', 'fecha_hasta', 'estado_id', 'medico_id']);

        $citas = $this->queryCitas($empresaId, $filtros)->get();
        $empresa = Empresa::findOrFail($empresaId);

        $pdf = Pdf::loadView('pdf.reporte_citas', compact('citas', 'empresa', 'filtros'))
            ->setPaper('legal', 'landscape');

        return $pdf->download('reporte-citas-' . now()->format('Y-m-d') . '.pdf');
    }

    public function citasExcel(Request $request): BinaryFileResponse
    {
        $empresaId = auth()->user()->empresa_id;
        $filtros   = $request->only(['fecha_desde', 'fecha_hasta', 'estado_id', 'medico_id']);

        return Excel::download(
            new CitasExport($empresaId, $filtros),
            'reporte-citas-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    // ── Pacientes ──────────────────────────────────────────────────────

    public function pacientesPdf(Request $request): Response
    {
        $empresaId = auth()->user()->empresa_id;
        $filtros   = $request->only(['sexo', 'fecha_nacimiento_desde', 'fecha_nacimiento_hasta', 'buscar']);

        $pacientes = $this->queryPacientes($empresaId, $filtros)->get();
        $empresa   = Empresa::findOrFail($empresaId);

        $pdf = Pdf::loadView('pdf.reporte_pacientes', compact('pacientes', 'empresa', 'filtros'))
            ->setPaper('letter', 'landscape');

        return $pdf->download('reporte-pacientes-' . now()->format('Y-m-d') . '.pdf');
    }

    public function pacientesExcel(Request $request): BinaryFileResponse
    {
        $empresaId = auth()->user()->empresa_id;
        $filtros   = $request->only(['sexo', 'fecha_nacimiento_desde', 'fecha_nacimiento_hasta', 'buscar']);

        return Excel::download(
            new PacientesExport($empresaId, $filtros),
            'reporte-pacientes-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    // ── Médicos ────────────────────────────────────────────────────────

    public function medicosPdf(): Response
    {
        $empresaId = auth()->user()->empresa_id;

        $medicos = \App\Models\Medico::where('empresa_id', $empresaId)
            ->with('usuario')
            ->orderBy('id')
            ->get();

        $empresa = Empresa::findOrFail($empresaId);

        $pdf = Pdf::loadView('pdf.reporte_medicos', compact('medicos', 'empresa'))
            ->setPaper('letter', 'landscape');

        return $pdf->download('reporte-medicos-' . now()->format('Y-m-d') . '.pdf');
    }

    public function medicosExcel(): BinaryFileResponse
    {
        $empresaId = auth()->user()->empresa_id;

        return Excel::download(
            new MedicosExport($empresaId),
            'reporte-medicos-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    // ── Helpers privados ───────────────────────────────────────────────

    private function queryCitas(int $empresaId, array $filtros)
    {
        $query = Cita::where('empresa_id', $empresaId)
            ->with(['medico.usuario', 'paciente', 'estado', 'modalidad', 'servicio']);

        if (!empty($filtros['fecha_desde'])) {
            $query->whereDate('fecha', '>=', $filtros['fecha_desde']);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $query->whereDate('fecha', '<=', $filtros['fecha_hasta']);
        }
        if (!empty($filtros['estado_id'])) {
            $query->where('estado_id', $filtros['estado_id']);
        }
        if (!empty($filtros['medico_id'])) {
            $query->where('medico_id', $filtros['medico_id']);
        }

        return $query->orderBy('fecha')->orderBy('hora');
    }

    private function queryPacientes(int $empresaId, array $filtros)
    {
        $query = Paciente::where('empresa_id', $empresaId);

        if (!empty($filtros['sexo'])) {
            $query->where('sexo', $filtros['sexo']);
        }
        if (!empty($filtros['fecha_nacimiento_desde'])) {
            $query->whereDate('fecha_nacimiento', '>=', $filtros['fecha_nacimiento_desde']);
        }
        if (!empty($filtros['fecha_nacimiento_hasta'])) {
            $query->whereDate('fecha_nacimiento', '<=', $filtros['fecha_nacimiento_hasta']);
        }
        if (!empty($filtros['buscar'])) {
            $t = $filtros['buscar'];
            $query->where(fn ($q) => $q->where('nombre_completo', 'like', "%{$t}%")
                                       ->orWhere('identificacion', 'like', "%{$t}%"));
        }

        return $query->orderBy('nombre_completo');
    }
}
