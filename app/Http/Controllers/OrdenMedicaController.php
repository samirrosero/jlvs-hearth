<?php

namespace App\Http\Controllers;

use App\Models\OrdenMedica;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdenMedicaController extends Controller
{
    public function index(): JsonResponse
    {
        $user    = auth()->user();
        $rol     = $user->rol?->nombre;

        $query = OrdenMedica::with('historiaClinica');

        if ($rol === 'paciente') {
            $query->where('paciente_id', $user->paciente?->id);
        } else {
            // Médico y admin solo ven órdenes de su empresa
            $query->whereHas('paciente', fn ($q) => $q->where('empresa_id', $user->empresa_id));
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'historia_clinica_id' => 'required|exists:historias_clinicas,id',
            'paciente_id'         => 'required|exists:pacientes,id',
            'tipo'                => 'required|string|max:100',
            'descripcion'         => 'required|string',
            'instrucciones'       => 'nullable|string',
        ]);

        $orden = OrdenMedica::create($data);

        return response()->json($orden, 201);
    }

    // Autorizar una orden (paciente, gestor o admin)
    public function update(Request $request, OrdenMedica $ordenMedica): JsonResponse
    {
        $data = $request->validate([
            'estado'         => 'required|in:autorizada,pendiente',
            'autorizado_via' => 'required_if:estado,autorizada|nullable|in:presencial,virtual',
        ]);

        if ($data['estado'] === 'autorizada') {
            $data['autorizado_en'] = now();
        }

        $ordenMedica->update($data);

        return response()->json($ordenMedica);
    }
}
