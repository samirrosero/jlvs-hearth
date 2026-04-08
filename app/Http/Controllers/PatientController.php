<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Paciente::where('empresa_id', auth()->user()->empresa_id);

        // Búsqueda por nombre o identificación
        if ($request->filled('buscar')) {
            $termino = $request->input('buscar');
            $query->where(function ($q) use ($termino) {
                $q->where('nombre_completo', 'like', "%{$termino}%")
                  ->orWhere('identificacion', 'like', "%{$termino}%");
            });
        }
        if ($request->filled('sexo')) {
            $query->where('sexo', $request->input('sexo'));
        }
        if ($request->filled('fecha_nacimiento_desde')) {
            $query->whereDate('fecha_nacimiento', '>=', $request->input('fecha_nacimiento_desde'));
        }
        if ($request->filled('fecha_nacimiento_hasta')) {
            $query->whereDate('fecha_nacimiento', '<=', $request->input('fecha_nacimiento_hasta'));
        }

        return response()->json($query->orderBy('nombre_completo')->get());
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
