<?php

namespace App\Http\Controllers;

use App\Models\EjecucionCita;
use App\Mail\ValoracionCitaMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\StoreAppointmentExecutionRequest;
use App\Http\Requests\UpdateAppointmentExecutionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentExecutionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', EjecucionCita::class);

        $empresaId = auth()->user()->empresa_id;

        $query = EjecucionCita::whereHas('cita', fn ($q) => $q->where('empresa_id', $empresaId))
            ->with('cita.paciente', 'cita.medico.usuario', 'historiaClinica');

        if ($request->filled('medico_id')) {
            $query->whereHas('cita', fn ($q) => $q->where('medico_id', $request->integer('medico_id')));
        }
        if ($request->filled('paciente_id')) {
            $query->whereHas('cita', fn ($q) => $q->where('paciente_id', $request->integer('paciente_id')));
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('inicio_atencion', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('inicio_atencion', '<=', $request->input('fecha_hasta'));
        }

        return response()->json($query->orderByDesc('inicio_atencion')->get());
    }

    public function store(StoreAppointmentExecutionRequest $request): JsonResponse
    {
        $ejecucion = EjecucionCita::create($request->validated());
        return response()->json($ejecucion->load('cita'), 201);
    }

    public function show(EjecucionCita $ejecucion): JsonResponse
    {
        $this->authorize('view', $ejecucion);
        return response()->json($ejecucion->load('cita.paciente', 'cita.medico.usuario', 'historiaClinica'));
    }

    public function update(UpdateAppointmentExecutionRequest $request, EjecucionCita $ejecucion): JsonResponse
    {
        $this->authorize('update', $ejecucion);

        $data = $request->validated();

        if (!empty($data['fin_atencion']) && $ejecucion->inicio_atencion) {
            $inicio = \Carbon\Carbon::parse($ejecucion->inicio_atencion);
            $fin    = \Carbon\Carbon::parse($data['fin_atencion']);
            $data['duracion_minutos'] = (int) $inicio->diffInMinutes($fin);

            $estadoAtendida = \App\Models\EstadoCita::where('nombre', 'like', '%tendida%')->first();
            if ($estadoAtendida) {
                $ejecucion->cita()->update(['estado_id' => $estadoAtendida->id]);

                // Enviar correo de valoración al paciente
                $cita = $ejecucion->cita()->with('paciente.usuario', 'empresa', 'medico.usuario', 'servicio')->first();
                $correo = $cita->paciente->correo ?? $cita->paciente->usuario?->email;
                if ($correo) {
                    Mail::to($correo)->send(new ValoracionCitaMail($cita));
                }
            }
        }

        $ejecucion->update($data);
        return response()->json($ejecucion);
    }

    public function destroy(EjecucionCita $ejecucion): JsonResponse
    {
        $this->authorize('delete', $ejecucion);
        $ejecucion->delete();
        return response()->json(null, 204);
    }
}
