<?php

namespace App\Http\Controllers;

use App\Models\HistoriaClinica;
use App\Http\Requests\StoreClinicalHistoryRequest;
use App\Http\Requests\UpdateClinicalHistoryRequest;
use Illuminate\Http\JsonResponse;

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
}
