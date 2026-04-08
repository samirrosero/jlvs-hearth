<?php

namespace App\Http\Controllers;

use App\Models\HistoriaClinica;
use App\Models\LogAuditoria;
use App\Http\Requests\StoreClinicalHistoryRequest;
use App\Http\Requests\UpdateClinicalHistoryRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;

class ClinicalHistoryController extends Controller
{
    public function index(): JsonResponse
    {
        $historias = HistoriaClinica::whereHas('paciente', function ($q) {
            $q->where('empresa_id', auth()->user()->empresa_id);
        })->with('paciente', 'ejecucionCita.cita.medico.usuario', 'recetasMedicas')->get();

        return response()->json($historias);
    }

    public function store(StoreClinicalHistoryRequest $request): JsonResponse
    {
        $historia = HistoriaClinica::create($request->validated());
        return response()->json($historia->load('paciente', 'recetasMedicas', 'documentosAdjuntos'), 201);
    }

    public function show(HistoriaClinica $historia): JsonResponse
    {
        $this->authorize('view', $historia);

        $usuario = auth()->user();
        LogAuditoria::create([
            'usuario_id' => $usuario->id,
            'empresa_id' => $usuario->empresa_id,
            'accion'     => 'ver',
            'modelo'     => 'HistoriaClinica',
            'modelo_id'  => $historia->id,
            'ip'         => Request::ip(),
            'detalles'   => null,
        ]);

        return response()->json($historia->load(
            'paciente',
            'ejecucionCita.cita.medico.usuario',
            'recetasMedicas',
            'documentosAdjuntos'
        ));
    }

    public function update(UpdateClinicalHistoryRequest $request, HistoriaClinica $historia): JsonResponse
    {
        $this->authorize('update', $historia);
        $historia->update($request->validated());
        return response()->json($historia);
    }

    public function destroy(HistoriaClinica $historia): JsonResponse
    {
        $this->authorize('delete', $historia);
        $historia->delete();
        return response()->json(null, 204);
    }

    public function pdf(HistoriaClinica $historia): Response
    {
        $this->authorize('view', $historia);

        $historia->load(
            'paciente',
            'ejecucionCita.cita.medico.usuario',
            'ejecucionCita.cita.modalidad',
            'ejecucionCita.cita.portafolio',
            'recetasMedicas'
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
}
