<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\Valoracion;

class MedicoValoracionesController extends Controller
{
    public function index()
    {
        $medico = auth()->user()->medico;

        // Valoraciones que han recibido este médico (solo con datos completos)
        $valoraciones = Valoracion::whereHas('cita', function ($query) use ($medico) {
            $query->where('medico_id', $medico->id);
        })
        ->whereHas('cita.paciente.usuario') // Solo valoraciones con pacientes que tienen usuario
        ->with(['cita.paciente.usuario', 'cita.servicio'])
        ->orderByDesc('created_at')
        ->paginate(10);

        // Estadísticas de valoraciones
        $estadisticas = [
            'total' => $valoraciones->total(),
            'promedio' => $valoraciones->count() > 0 ? round($valoraciones->avg('puntuacion'), 1) : 0,
            'excelente' => $valoraciones->where('puntuacion', '>=', 4)->count(),
            'bueno' => $valoraciones->where('puntuacion', 3)->count(),
            'regular' => $valoraciones->where('puntuacion', 2)->count(),
            'malo' => $valoraciones->where('puntuacion', 1)->count(),
        ];

        return view('medico.valoraciones.index', compact('valoraciones', 'estadisticas'));
    }
}
