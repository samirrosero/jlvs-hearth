<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HorarioMedico;
use App\Models\Medico;
use Illuminate\Http\Request;

class AdminHorarioController extends Controller
{
    private const DIAS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        0 => 'Domingo',
    ];

    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $medicos = Medico::where('empresa_id', $empresaId)
            ->with('usuario')
            ->orderBy('id')
            ->get();

        $medicoSeleccionado = null;
        $horarios           = collect();

        if ($request->filled('medico_id')) {
            $medicoSeleccionado = $medicos->firstWhere('id', $request->integer('medico_id'));

            if ($medicoSeleccionado) {
                $registros = HorarioMedico::where('medico_id', $medicoSeleccionado->id)
                    ->where('empresa_id', $empresaId)
                    ->get()
                    ->keyBy('dia_semana');

                // Construye los 7 días siempre, con los datos guardados si existen
                foreach (self::DIAS as $num => $nombre) {
                    $horarios[$num] = [
                        'dia'         => $num,
                        'nombre'      => $nombre,
                        'activo'      => isset($registros[$num]) && $registros[$num]->activo,
                        'hora_inicio' => $registros[$num]->hora_inicio ?? '08:00',
                        'hora_fin'    => $registros[$num]->hora_fin    ?? '17:00',
                    ];
                }
            }
        }

        return view('admin.horarios.index', compact('medicos', 'medicoSeleccionado', 'horarios'));
    }

    public function guardar(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        // Los navegadores pueden enviar HH:MM:SS — recortamos a HH:MM
        $dias = $request->input('dias', []);
        foreach ($dias as $num => &$dia) {
            if (!empty($dia['hora_inicio'])) $dia['hora_inicio'] = substr($dia['hora_inicio'], 0, 5);
            if (!empty($dia['hora_fin']))    $dia['hora_fin']    = substr($dia['hora_fin'],    0, 5);
        }
        unset($dia);
        $request->merge(['dias' => $dias]);

        $request->validate([
            'medico_id'          => ['required', 'exists:medicos,id'],
            'dias'               => ['nullable', 'array'],
            'dias.*.hora_inicio' => ['required_with:dias.*.activo', 'date_format:H:i'],
            'dias.*.hora_fin'    => ['required_with:dias.*.activo', 'date_format:H:i'],
        ]);

        // Validación explícita: hora_fin debe ser posterior a hora_inicio en cada día activo
        $diasInput = $request->input('dias', []);
        $erroresDias = [];
        foreach ($diasInput as $num => $dia) {
            if (!isset($dia['activo'])) continue;
            $inicio = $dia['hora_inicio'] ?? '';
            $fin    = $dia['hora_fin']    ?? '';
            if ($inicio && $fin && $fin <= $inicio) {
                $nombre = self::DIAS[$num] ?? "Día $num";
                $erroresDias["dias.{$num}.hora_fin"] = ["{$nombre}: la hora de fin ({$fin}) debe ser posterior a la hora de inicio ({$inicio})."];
            }
        }
        if (!empty($erroresDias)) {
            return back()->withErrors($erroresDias)->withInput();
        }

        // Verificar que el médico pertenece a esta empresa
        $medico = Medico::where('id', $request->medico_id)
            ->where('empresa_id', $empresaId)
            ->firstOrFail();

        // Eliminar horarios existentes y recrear
        HorarioMedico::where('medico_id', $medico->id)
            ->where('empresa_id', $empresaId)
            ->delete();

        $dias = $request->input('dias', []);

        foreach (array_keys(self::DIAS) as $num) {
            $dia = $dias[$num] ?? [];
            $activo = isset($dia['activo']);

            if ($activo) {
                HorarioMedico::create([
                    'medico_id'   => $medico->id,
                    'empresa_id'  => $empresaId,
                    'dia_semana'  => $num,
                    'hora_inicio' => $dia['hora_inicio'],
                    'hora_fin'    => $dia['hora_fin'],
                    'activo'      => true,
                ]);
            }
        }

        return redirect()
            ->route('admin.horarios', ['medico_id' => $medico->id])
            ->with('exito', 'Horario de ' . $medico->usuario->nombre . ' guardado correctamente.');
    }
}
