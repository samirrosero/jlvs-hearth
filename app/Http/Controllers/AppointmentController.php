<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    public function index(): JsonResponse
    {
        $user  = auth()->user();
        $query = Cita::where('empresa_id', $user->empresa_id)
            ->with(['medico.usuario', 'paciente', 'estado', 'modalidad', 'portafolio']);

        if ($user->rol?->nombre === 'medico') {
            $query->where('medico_id', $user->medico?->id);
        } elseif ($user->rol?->nombre === 'paciente') {
            $query->where('paciente_id', $user->paciente?->id);
        }

        return response()->json($query->orderBy('fecha')->orderBy('hora')->get());
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $cita = Cita::create(array_merge(
            $request->validated(),
            ['empresa_id' => auth()->user()->empresa_id]
        ));
        return response()->json($cita->load('medico.usuario', 'paciente', 'estado', 'modalidad'), 201);
    }

    public function show(Cita $cita): JsonResponse
    {
        $this->authorize('view', $cita);
        return response()->json($cita->load('medico.usuario', 'paciente', 'estado', 'modalidad', 'portafolio', 'ejecucion'));
    }

    public function update(UpdateAppointmentRequest $request, Cita $cita): JsonResponse
    {
        $this->authorize('update', $cita);
        $cita->update($request->validated());
        return response()->json($cita->load('medico.usuario', 'paciente', 'estado', 'modalidad'));
    }

    public function destroy(Cita $cita): JsonResponse
    {
        $this->authorize('delete', $cita);
        $cita->update(['activo' => false]);
        return response()->json(['message' => 'Cita cancelada.']);
    }
}
