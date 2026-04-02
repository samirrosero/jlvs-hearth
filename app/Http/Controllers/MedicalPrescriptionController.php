<?php

namespace App\Http\Controllers;

use App\Models\RecetaMedica;
use App\Http\Requests\StoreMedicalPrescriptionRequest;
use App\Http\Requests\UpdateMedicalPrescriptionRequest;
use Illuminate\Http\JsonResponse;

class MedicalPrescriptionController extends Controller
{
    public function index(): JsonResponse
    {
        $recetas = RecetaMedica::whereHas('historiaClinica.paciente', function ($q) {
            $q->where('empresa_id', auth()->user()->empresa_id);
        })->with('historiaClinica.paciente')->get();

        return response()->json($recetas);
    }

    public function store(StoreMedicalPrescriptionRequest $request): JsonResponse
    {
        $receta = RecetaMedica::create($request->validated());
        return response()->json($receta, 201);
    }

    public function show(RecetaMedica $receta): JsonResponse
    {
        $this->authorize('view', $receta);
        return response()->json($receta->load('historiaClinica.paciente'));
    }

    public function update(UpdateMedicalPrescriptionRequest $request, RecetaMedica $receta): JsonResponse
    {
        $this->authorize('update', $receta);
        $receta->update($request->validated());
        return response()->json($receta);
    }

    public function destroy(RecetaMedica $receta): JsonResponse
    {
        $this->authorize('delete', $receta);
        $receta->delete();
        return response()->json(null, 204);
    }
}
