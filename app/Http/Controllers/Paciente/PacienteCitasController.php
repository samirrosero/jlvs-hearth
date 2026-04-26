<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\EstadoCita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PacienteCitasController extends Controller
{
    public function index()
    {
        $pacienteId = auth()->user()->paciente->id;

        $citas = Cita::where('paciente_id', $pacienteId)
            ->with('medico.usuario', 'estado', 'servicio', 'modalidad')
            ->when(request('estado_id'), fn ($q) => $q->where('estado_id', request('estado_id')))
            ->orderByDesc('fecha')
            ->paginate(10)
            ->withQueryString();

        $estados = EstadoCita::all();

        return view('paciente.citas.index', compact('citas', 'estados'));
    }

    public function store(Request $request)
    {
        // ✅ VALIDACIÓN MANUAL (evita JSON)
        $validator = Validator::make($request->all(), [
            'medico_id' => [
                'required',
                'exists:medicos,id',
                new \App\Rules\MedicoDisponible()
            ],
            'servicio_id' => 'required|exists:servicios,id',
            'modalidad_id' => 'required|exists:modalidades_cita,id',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required|date_format:H:i',
        ]);

        // 🔴 SI FALLA → REDIRIGE CON ERRORES
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // ✅ SI TODO BIEN → GUARDA
        $user = auth()->user();
        $paciente = $user->paciente;
        $empresaId = $user->empresa_id;

        $estadoInicial = EstadoCita::where('nombre', 'Agendada')->first();

        Cita::create([
            'empresa_id' => $empresaId,
            'paciente_id' => $paciente->id,
            'medico_id' => $request->medico_id,
            'servicio_id' => $request->servicio_id,
            'modalidad_id' => $request->modalidad_id,
            'estado_id' => $estadoInicial ? $estadoInicial->id : 1,
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'motivo_consulta' => 'Agendado desde panel de paciente',
            'activo' => true
        ]);

        return redirect()->route('paciente.citas')
            ->with('success', 'Cita agendada exitosamente.');
    }
}