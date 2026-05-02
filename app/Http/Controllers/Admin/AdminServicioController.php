<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminServicioController extends Controller
{
    public function index(): View
    {
        $servicios = Servicio::where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('nombre')
            ->get();

        return view('admin.servicios.index', compact('servicios'));
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $request->validate([
            'nombre'           => [
                'required', 'string', 'max:150',
                Rule::unique('servicios')->where('empresa_id', $empresaId),
            ],
            'descripcion'      => ['nullable', 'string', 'max:500'],
            'duracion_minutos' => ['required', 'integer', 'min:5', 'max:480'],
        ], [
            'nombre.required'          => 'El nombre del servicio es obligatorio.',
            'nombre.unique'            => 'Ya existe un servicio con ese nombre en su institución.',
            'duracion_minutos.required' => 'La duración en minutos es obligatoria.',
            'duracion_minutos.min'     => 'La duración mínima es de 5 minutos.',
            'duracion_minutos.max'     => 'La duración máxima es de 480 minutos (8 horas).',
        ]);

        Servicio::create([
            'empresa_id'       => $empresaId,
            'nombre'           => $request->nombre,
            'descripcion'      => $request->descripcion,
            'duracion_minutos' => $request->duracion_minutos,
            'activo'           => true,
        ]);

        return redirect()->route('admin.servicios.index')
            ->with('exito', 'Servicio "' . $request->nombre . '" registrado correctamente.');
    }

    public function edit(Servicio $servicio): View
    {
        abort_if($servicio->empresa_id !== auth()->user()->empresa_id, 403);

        $servicios = Servicio::where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('nombre')
            ->get();

        return view('admin.servicios.index', compact('servicios', 'servicio'));
    }

    public function update(Request $request, Servicio $servicio): RedirectResponse
    {
        abort_if($servicio->empresa_id !== auth()->user()->empresa_id, 403);

        $empresaId = auth()->user()->empresa_id;

        $request->validate([
            'nombre'           => [
                'required', 'string', 'max:150',
                Rule::unique('servicios')->where('empresa_id', $empresaId)->ignore($servicio->id),
            ],
            'descripcion'      => ['nullable', 'string', 'max:500'],
            'duracion_minutos' => ['required', 'integer', 'min:5', 'max:480'],
        ], [
            'nombre.required'           => 'El nombre del servicio es obligatorio.',
            'nombre.unique'             => 'Ya existe un servicio con ese nombre en su institución.',
            'duracion_minutos.required' => 'La duración en minutos es obligatoria.',
            'duracion_minutos.min'      => 'La duración mínima es de 5 minutos.',
            'duracion_minutos.max'      => 'La duración máxima es de 480 minutos (8 horas).',
        ]);

        $servicio->update([
            'nombre'           => $request->nombre,
            'descripcion'      => $request->descripcion,
            'duracion_minutos' => $request->duracion_minutos,
        ]);

        return redirect()->route('admin.servicios.index')
            ->with('exito', 'Servicio "' . $request->nombre . '" actualizado correctamente.');
    }

    public function destroy(Servicio $servicio): RedirectResponse
    {
        abort_if($servicio->empresa_id !== auth()->user()->empresa_id, 403);

        $servicio->update(['activo' => false]);

        return redirect()->route('admin.servicios.index')
            ->with('exito', 'Servicio desactivado correctamente.');
    }
}
