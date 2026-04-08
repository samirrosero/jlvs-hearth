<?php

namespace App\Http\Controllers;

use App\Models\Valoracion;
use App\Http\Requests\StoreValoracionRequest;
use Illuminate\Http\JsonResponse;

class ValoracionController extends Controller
{
    /**
     * Admin: lista todas las valoraciones de su empresa con promedio por médico.
     * Paciente: lista solo sus propias valoraciones.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        if ($user->rol?->nombre === 'paciente') {
            $valoraciones = Valoracion::where('paciente_id', $user->paciente?->id)
                ->with('cita.medico.usuario', 'cita.estado')
                ->orderByDesc('created_at')
                ->get();

            return response()->json($valoraciones);
        }

        // Admin / médico — filtrado por empresa
        $valoraciones = Valoracion::whereHas('cita', fn ($q) => $q->where('empresa_id', $user->empresa_id))
            ->with('cita.medico.usuario', 'paciente')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($valoraciones);
    }

    /**
     * Solo el paciente puede crear su propia valoración.
     * La validación garantiza que la cita le pertenece y no tiene valoración previa.
     */
    public function store(StoreValoracionRequest $request): JsonResponse
    {
        $valoracion = Valoracion::create(array_merge(
            $request->validated(),
            ['paciente_id' => auth()->user()->paciente->id]
        ));

        return response()->json($valoracion->load('cita.medico.usuario'), 201);
    }

    /**
     * Ver una valoración (admin de la empresa o el paciente dueño).
     */
    public function show(Valoracion $valoracion): JsonResponse
    {
        $user = auth()->user();

        abort_unless(
            $user->empresa_id === $valoracion->cita?->empresa_id ||
            $user->paciente?->id === $valoracion->paciente_id,
            403
        );

        return response()->json($valoracion->load('cita.medico.usuario', 'paciente'));
    }

    /**
     * Resumen de valoraciones por médico — solo admin.
     */
    public function resumenMedicos(): JsonResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $resumen = Valoracion::whereHas('cita', fn ($q) => $q->where('empresa_id', $empresaId))
            ->join('citas', 'valoraciones.cita_id', '=', 'citas.id')
            ->join('medicos', 'citas.medico_id', '=', 'medicos.id')
            ->join('users', 'medicos.usuario_id', '=', 'users.id')
            ->selectRaw('
                medicos.id as medico_id,
                users.nombre as medico,
                medicos.especialidad,
                COUNT(valoraciones.id) as total_valoraciones,
                ROUND(AVG(valoraciones.puntuacion), 2) as promedio,
                SUM(CASE WHEN valoraciones.puntuacion = 5 THEN 1 ELSE 0 END) as cinco_estrellas,
                SUM(CASE WHEN valoraciones.puntuacion = 4 THEN 1 ELSE 0 END) as cuatro_estrellas,
                SUM(CASE WHEN valoraciones.puntuacion = 3 THEN 1 ELSE 0 END) as tres_estrellas,
                SUM(CASE WHEN valoraciones.puntuacion = 2 THEN 1 ELSE 0 END) as dos_estrellas,
                SUM(CASE WHEN valoraciones.puntuacion = 1 THEN 1 ELSE 0 END) as una_estrella
            ')
            ->groupBy('medicos.id', 'users.nombre', 'medicos.especialidad')
            ->orderByDesc('promedio')
            ->get();

        return response()->json($resumen);
    }
}
