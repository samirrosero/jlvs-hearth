<?php

namespace App\Http\Controllers;

use App\Models\SignosVitales;
use App\Http\Requests\StoreSignosVitalesRequest;
use App\Http\Requests\UpdateSignosVitalesRequest;
use Illuminate\Http\JsonResponse;

class SignosVitalesController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $query = SignosVitales::whereHas('paciente', function ($q) use ($user) {
            $q->where('empresa_id', $user->empresa_id);
        });

        // El paciente solo ve sus propios signos vitales
        if ($user->rol?->nombre === 'paciente') {
            $query->where('paciente_id', $user->paciente?->id);
        }

        return response()->json($query->with('ejecucionCita')->get());
    }

    public function store(StoreSignosVitalesRequest $request): JsonResponse
    {
        $signos = SignosVitales::create($request->validated());

        return response()->json($signos, 201);
    }

    public function show(SignosVitales $signosVitales): JsonResponse
    {
        $this->authorize('view', $signosVitales);

        return response()->json($signosVitales);
    }

    public function update(UpdateSignosVitalesRequest $request, SignosVitales $signosVitales): JsonResponse
    {
        $this->authorize('update', $signosVitales);
        $signosVitales->update($request->validated());

        return response()->json($signosVitales);
    }

    public function destroy(SignosVitales $signosVitales): JsonResponse
    {
        $this->authorize('delete', $signosVitales);
        $signosVitales->delete();

        return response()->json(['message' => 'Signos vitales eliminados.']);
    }
}
