<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\HorarioMedico;
use Illuminate\View\View;

class MedicoHorarioController extends Controller
{
    public function index(): View
    {
        $medico = auth()->user()->medico;

        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        $horarios = HorarioMedico::where('medico_id', $medico->id)
            ->where('activo', true)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get()
            ->groupBy('dia_semana');

        return view('medico.horario.index', compact('horarios', 'dias'));
    }
}
