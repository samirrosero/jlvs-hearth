<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portafolio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPortafolioController extends Controller
{
    public function index(): View
    {
        $portafolios = Portafolio::where('empresa_id', auth()->user()->empresa_id)
            ->withCount('citas')
            ->orderBy('nombre_convenio')
            ->get();

        return view('admin.portafolios.index', compact('portafolios'));
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $request->validate([
            'nombre_convenio' => [
                'required', 'string', 'max:255',
                Rule::unique('portafolios')->where('empresa_id', $empresaId),
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ], [
            'nombre_convenio.required' => 'El nombre del convenio es obligatorio.',
            'nombre_convenio.unique'   => 'Ya existe un convenio con ese nombre en su institución.',
        ]);

        Portafolio::create([
            'empresa_id'      => $empresaId,
            'nombre_convenio' => $request->nombre_convenio,
            'descripcion'     => $request->descripcion,
        ]);

        return redirect()->route('admin.portafolios.index')
            ->with('exito', 'Convenio "' . $request->nombre_convenio . '" registrado correctamente.');
    }

    public function edit(Portafolio $portafolio): View
    {
        abort_if($portafolio->empresa_id !== auth()->user()->empresa_id, 403);

        $portafolios = Portafolio::where('empresa_id', auth()->user()->empresa_id)
            ->withCount('citas')
            ->orderBy('nombre_convenio')
            ->get();

        return view('admin.portafolios.index', compact('portafolios', 'portafolio'));
    }

    public function update(Request $request, Portafolio $portafolio): RedirectResponse
    {
        abort_if($portafolio->empresa_id !== auth()->user()->empresa_id, 403);

        $empresaId = auth()->user()->empresa_id;

        $request->validate([
            'nombre_convenio' => [
                'required', 'string', 'max:255',
                Rule::unique('portafolios')->where('empresa_id', $empresaId)->ignore($portafolio->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ], [
            'nombre_convenio.required' => 'El nombre del convenio es obligatorio.',
            'nombre_convenio.unique'   => 'Ya existe un convenio con ese nombre en su institución.',
        ]);

        $portafolio->update([
            'nombre_convenio' => $request->nombre_convenio,
            'descripcion'     => $request->descripcion,
        ]);

        return redirect()->route('admin.portafolios.index')
            ->with('exito', 'Convenio "' . $request->nombre_convenio . '" actualizado correctamente.');
    }

    public function destroy(Portafolio $portafolio): RedirectResponse
    {
        abort_if($portafolio->empresa_id !== auth()->user()->empresa_id, 403);

        if ($portafolio->citas()->exists()) {
            return redirect()->route('admin.portafolios.index')
                ->with('error', 'No se puede eliminar el convenio "' . $portafolio->nombre_convenio . '" porque tiene citas asociadas.');
        }

        $portafolio->delete();

        return redirect()->route('admin.portafolios.index')
            ->with('exito', 'Convenio eliminado correctamente.');
    }
}
