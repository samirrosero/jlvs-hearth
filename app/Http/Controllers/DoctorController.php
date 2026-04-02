<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    public function index(): JsonResponse
    {
        $medicos = Medico::where('empresa_id', auth()->user()->empresa_id)
            ->with('usuario')
            ->get();
        return response()->json($medicos);
    }

    public function store(StoreDoctorRequest $request): JsonResponse
    {
        $medico = Medico::create(array_merge(
            $request->validated(),
            ['empresa_id' => auth()->user()->empresa_id]
        ));
        return response()->json($medico->load('usuario'), 201);
    }

    public function show(Medico $medico): JsonResponse
    {
        $this->authorize('view', $medico);
        return response()->json($medico->load('usuario', 'citas.paciente'));
    }

    public function update(UpdateDoctorRequest $request, Medico $medico): JsonResponse
    {
        $this->authorize('update', $medico);
        $medico->update($request->validated());
        return response()->json($medico->load('usuario'));
    }

    public function destroy(Medico $medico): JsonResponse
    {
        $this->authorize('delete', $medico);
        $medico->delete();
        return response()->json(null, 204);
    }
}
