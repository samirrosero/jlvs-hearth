<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use Illuminate\Http\JsonResponse;

class PatientController extends Controller
{
    public function index(): JsonResponse
    {
        $pacientes = Paciente::where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('nombre_completo')
            ->get();
        return response()->json($pacientes);
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        $paciente = Paciente::create(array_merge(
            $request->validated(),
            ['empresa_id' => auth()->user()->empresa_id]
        ));
        return response()->json($paciente, 201);
    }

    public function show(Paciente $paciente): JsonResponse
    {
        $this->authorize('view', $paciente);
        return response()->json($paciente->load('citas.medico.usuario', 'citas.estado', 'citas.modalidad'));
    }

    public function update(UpdatePatientRequest $request, Paciente $paciente): JsonResponse
    {
        $this->authorize('update', $paciente);
        $paciente->update($request->validated());
        return response()->json($paciente);
    }

    public function destroy(Paciente $paciente): JsonResponse
    {
        $this->authorize('delete', $paciente);
        $paciente->delete();
        return response()->json(null, 204);
    }
}
