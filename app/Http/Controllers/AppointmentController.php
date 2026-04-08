<?php

namespace App\Http\Controllers;

use App\Mail\CitaAgendadaMail;
use App\Models\Cita;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user  = auth()->user();
        $query = Cita::where('empresa_id', $user->empresa_id)
            ->with(['medico.usuario', 'paciente', 'estado', 'modalidad', 'portafolio']);

        // Restricción por rol
        if ($user->rol?->nombre === 'medico') {
            $query->where('medico_id', $user->medico?->id);
        } elseif ($user->rol?->nombre === 'paciente') {
            $query->where('paciente_id', $user->paciente?->id);
        }

        // Filtros opcionales (solo para admin y gestor_citas)
        if ($request->filled('medico_id')) {
            $query->where('medico_id', $request->integer('medico_id'));
        }
        if ($request->filled('paciente_id')) {
            $query->where('paciente_id', $request->integer('paciente_id'));
        }
        if ($request->filled('estado_id')) {
            $query->where('estado_id', $request->integer('estado_id'));
        }
        if ($request->filled('modalidad_id')) {
            $query->where('modalidad_id', $request->integer('modalidad_id'));
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }
        if ($request->filled('activo')) {
            $query->where('activo', filter_var($request->input('activo'), FILTER_VALIDATE_BOOLEAN));
        }
        // Búsqueda por nombre de paciente
        if ($request->filled('buscar')) {
            $termino = $request->input('buscar');
            $query->whereHas('paciente', fn ($q) => $q->where('nombre_completo', 'like', "%{$termino}%"));
        }

        return response()->json($query->orderBy('fecha')->orderBy('hora')->get());
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $cita = Cita::create(array_merge(
            $request->validated(),
            ['empresa_id' => auth()->user()->empresa_id]
        ));

        $cita->load('medico.usuario', 'paciente', 'estado', 'modalidad', 'portafolio', 'servicio', 'empresa');

        // Enviar confirmación por correo si el paciente tiene correo registrado
        $correoPaciente = $cita->paciente?->correo ?? $cita->paciente?->usuario?->email;
        if ($correoPaciente) {
            Mail::to($correoPaciente)->queue(new CitaAgendadaMail($cita));
        }

        return response()->json($cita, 201);
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
