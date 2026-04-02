<?php

namespace App\Http\Controllers;

use App\Models\EjecucionCita;
use App\Http\Requests\StoreAppointmentExecutionRequest;
use App\Http\Requests\UpdateAppointmentExecutionRequest;
use Illuminate\Http\JsonResponse;

class AppointmentExecutionController extends Controller
{
    public function index(): JsonResponse
    {
        $ejecuciones = EjecucionCita::whereHas('cita', function ($q) {
            $q->where('empresa_id', auth()->user()->empresa_id);
        })->with('cita.paciente', 'cita.medico.usuario', 'historiaClinica')->get();

        return response()->json($ejecuciones);
    }

    public function store(StoreAppointmentExecutionRequest $request): JsonResponse
    {
        $ejecucion = EjecucionCita::create($request->validated());
        return response()->json($ejecucion->load('cita'), 201);
    }

    public function show(EjecucionCita $ejecucion): JsonResponse
    {
        $this->authorize('view', $ejecucion);
        return response()->json($ejecucion->load('cita.paciente', 'cita.medico.usuario', 'historiaClinica'));
    }

    public function update(UpdateAppointmentExecutionRequest $request, EjecucionCita $ejecucion): JsonResponse
    {
        $this->authorize('update', $ejecucion);
        $ejecucion->update($request->validated());
        return response()->json($ejecucion);
    }

    public function destroy(EjecucionCita $ejecucion): JsonResponse
    {
        $this->authorize('delete', $ejecucion);
        $ejecucion->delete();
        return response()->json(null, 204);
    }
}
