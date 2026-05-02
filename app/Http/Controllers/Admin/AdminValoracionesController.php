<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Valoracion;
use App\Models\Medico;

class AdminValoracionesController extends Controller
{
    public function index()
    {
        // Todas las valoraciones del sistema (solo con datos completos)
        $valoraciones = Valoracion::whereHas('cita.paciente.usuario') // Solo con pacientes que tienen usuario
            ->whereHas('cita.medico.usuario') // Solo con médicos que tienen usuario
            ->with(['cita.medico.usuario', 'cita.paciente.usuario', 'cita.servicio'])
            ->orderByDesc('created_at')
            ->paginate(15);

        // Estadísticas generales
        $estadisticas = [
            'total' => $valoraciones->total(),
            'promedio_general' => $valoraciones->count() > 0 ? round($valoraciones->avg('puntuacion'), 1) : 0,
            'excelente' => $valoraciones->where('puntuacion', '>=', 4)->count(),
            'bueno' => $valoraciones->where('puntuacion', 3)->count(),
            'regular' => $valoraciones->where('puntuacion', 2)->count(),
            'malo' => $valoraciones->where('puntuacion', 1)->count(),
        ];

        // Top médicos por promedio de valoración
        $topMedicos = Medico::with('usuario')
            ->withCount(['citas as valoraciones_count' => function ($query) {
                $query->whereHas('valoracion');
            }])
            ->withAvg('citas as promedio_valoracion', 'valoracion.puntuacion')
            ->having('valoraciones_count', '>', 0)
            ->orderByDesc('promedio_valoracion')
            ->limit(5)
            ->get();

        return view('admin.valoraciones.index', compact('valoraciones', 'estadisticas', 'topMedicos'));
    }
}
