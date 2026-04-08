<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Medico::where('empresa_id', auth()->user()->empresa_id)
            ->with('usuario');

        // Búsqueda por nombre de usuario o especialidad
        if ($request->filled('buscar')) {
            $termino = $request->input('buscar');
            $query->where(function ($q) use ($termino) {
                $q->where('especialidad', 'like', "%{$termino}%")
                  ->orWhereHas('usuario', fn ($u) => $u->where('nombre', 'like', "%{$termino}%"));
            });
        }
        if ($request->filled('especialidad')) {
            $query->where('especialidad', 'like', '%' . $request->input('especialidad') . '%');
        }

        return response()->json($query->get());
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
