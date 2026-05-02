<?php

namespace App\Http\Controllers;

use App\Models\ListaEspera;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListaEsperaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $query = ListaEspera::where('empresa_id', $empresaId)
            ->with('paciente', 'medico.usuario', 'servicio', 'cita');

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }
        if ($request->filled('fecha')) {
            $query->whereDate('fecha_solicitada', $request->input('fecha'));
        }
        if ($request->filled('medico_id')) {
            $query->where('medico_id', $request->integer('medico_id'));
        }
        if ($request->filled('identificacion')) {
            $query->whereHas('paciente', fn ($q) =>
                $q->where('identificacion', 'like', $request->input('identificacion') . '%')
            );
        }

        return response()->json($query->orderBy('created_at')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'paciente_id'      => ['required', 'exists:pacientes,id'],
            'medico_id'        => ['nullable', 'exists:medicos,id'],
            'servicio_id'      => ['nullable', 'exists:servicios,id'],
            'fecha_solicitada' => ['required', 'date', 'after_or_equal:today'],
            'notas'            => ['nullable', 'string'],
        ]);

        $data['empresa_id'] = $empresaId;
        $data['estado']     = 'esperando';

        $registro = ListaEspera::create($data);

        return response()->json(
            $registro->load('paciente', 'medico.usuario', 'servicio'),
            201
        );
    }

    public function update(Request $request, ListaEspera $listaEspera): JsonResponse
    {
        $data = $request->validate([
            'estado'   => ['required', 'in:esperando,asignado,descartado'],
            'cita_id'  => ['nullable', 'exists:citas,id'],
            'notas'    => ['nullable', 'string'],
        ]);

        $listaEspera->update($data);

        return response()->json($listaEspera->load('paciente', 'medico.usuario', 'servicio', 'cita'));
    }

    public function destroy(ListaEspera $listaEspera): JsonResponse
    {
        $listaEspera->delete();
        return response()->json(null, 204);
    }
}
