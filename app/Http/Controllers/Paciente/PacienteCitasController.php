<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\EstadoCita;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PacienteCitasController extends Controller
{
    public function index()
    {
        $pacienteId = auth()->user()->paciente->id;

        $citas = Cita::where('paciente_id', $pacienteId)
            ->with('medico.usuario', 'estado', 'servicio.precios', 'modalidad')
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

    public function videollamada(Cita $cita)
    {
        abort_if($cita->paciente_id !== auth()->user()->paciente->id, 403);
        abort_if(strtolower($cita->modalidad->nombre ?? '') !== 'telemedicina', 404);

        $cita->load('medico.usuario', 'estado', 'modalidad', 'servicio');

        return view('paciente.citas.videollamada', compact('cita'));
    }

    public function cancelar(Cita $cita): RedirectResponse
    {
        abort_if($cita->paciente_id !== auth()->user()->paciente->id, 403);

        $cancelables = EstadoCita::whereIn('nombre', ['Pendiente', 'Confirmada'])->pluck('id');

        if (! $cancelables->contains($cita->estado_id)) {
            return back()->with('error', 'Esta cita no puede cancelarse porque ya fue atendida o ya está cancelada.');
        }

        if (Carbon::parse($cita->fecha)->isPast()) {
            return back()->with('error', 'No puedes cancelar una cita de una fecha pasada.');
        }

        $estadoCancelada = EstadoCita::where('nombre', 'Cancelada')->first();

        $cita->update([
            'estado_id' => $estadoCancelada->id,
            'activo'    => false,
        ]);

        return back()->with('success', 'Cita cancelada correctamente.');
    }
}