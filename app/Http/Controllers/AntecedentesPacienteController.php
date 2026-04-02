<?php

namespace App\Http\Controllers;

use App\Models\AntecedentesPaciente;
use App\Http\Requests\StoreAntecedentesPacienteRequest;
use App\Http\Requests\UpdateAntecedentesPacienteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AntecedentesPacienteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = AntecedentesPaciente::whereHas('paciente', function ($q) use ($user) {
            $q->where('empresa_id', $user->empresa_id);
        });

        // El paciente solo ve sus propios antecedentes
        if ($user->rol?->nombre === 'paciente') {
            $query->where('paciente_id', $user->paciente?->id);
        }

        // Permite filtrar por paciente: GET /antecedentes?paciente_id=3
        if ($request->filled('paciente_id')) {
            $query->where('paciente_id', $request->integer('paciente_id'));
        }

        // Permite filtrar por tipo: GET /antecedentes?tipo=alergico
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->string('tipo'));
        }

        return response()->json($query->get());
    }

    public function store(StoreAntecedentesPacienteRequest $request): JsonResponse
    {
        $antecedente = AntecedentesPaciente::create($request->validated());

        return response()->json($antecedente, 201);
    }

    public function show(AntecedentesPaciente $antecedente): JsonResponse
    {
        $this->authorize('view', $antecedente);

        return response()->json($antecedente);
    }

    public function update(UpdateAntecedentesPacienteRequest $request, AntecedentesPaciente $antecedente): JsonResponse
    {
        $this->authorize('update', $antecedente);
        $antecedente->update($request->validated());

        return response()->json($antecedente);
    }

    public function destroy(AntecedentesPaciente $antecedente): JsonResponse
    {
        $this->authorize('delete', $antecedente);
        $antecedente->delete();

        return response()->json(['message' => 'Antecedente eliminado.']);
    }
}
