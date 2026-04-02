<?php

namespace App\Http\Controllers;

use App\Models\HorarioMedico;
use App\Http\Requests\StoreHorarioMedicoRequest;
use App\Http\Requests\UpdateHorarioMedicoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HorarioMedicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $query = HorarioMedico::where('empresa_id', $empresaId);

        // Permite filtrar por médico: GET /horarios?medico_id=5
        if ($request->filled('medico_id')) {
            $query->where('medico_id', $request->integer('medico_id'));
        }

        // Permite filtrar por día: GET /horarios?dia=1
        if ($request->filled('dia')) {
            $query->where('dia_semana', $request->integer('dia'));
        }

        return response()->json($query->orderBy('medico_id')->orderBy('dia_semana')->orderBy('hora_inicio')->get());
    }

    public function store(StoreHorarioMedicoRequest $request): JsonResponse
    {
        $horario = HorarioMedico::create(
            array_merge($request->validated(), ['empresa_id' => auth()->user()->empresa_id])
        );

        return response()->json($horario, 201);
    }

    public function show(HorarioMedico $horario): JsonResponse
    {
        $this->authorize('view', $horario);

        return response()->json($horario);
    }

    public function update(UpdateHorarioMedicoRequest $request, HorarioMedico $horario): JsonResponse
    {
        $this->authorize('update', $horario);
        $horario->update($request->validated());

        return response()->json($horario);
    }

    public function destroy(HorarioMedico $horario): JsonResponse
    {
        $this->authorize('delete', $horario);
        $horario->delete();

        return response()->json(['message' => 'Horario eliminado.']);
    }
}
