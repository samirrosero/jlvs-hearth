<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portafolio;
use App\Models\PrecioServicio;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPrecioServicioController extends Controller
{
    /**
     * Mostrar el formulario para gestionar precios de un servicio
     */
    public function editarPrecios(Servicio $servicio): View
    {
        abort_if($servicio->empresa_id !== auth()->user()->empresa_id, 403);

        $empresaId = auth()->user()->empresa_id;

        // Obtener todos los portafolios de la empresa
        $portafolios = Portafolio::where('empresa_id', $empresaId)
            ->orderBy('nombre_convenio')
            ->get();

        // Obtener precios actuales del servicio (indexados por portafolio_id)
        $preciosActuales = PrecioServicio::where('servicio_id', $servicio->id)
            ->where('empresa_id', $empresaId)
            ->get()
            ->keyBy('portafolio_id');

        return view('admin.servicios.precios', compact('servicio', 'portafolios', 'preciosActuales'));
    }

    /**
     * Actualizar los precios de un servicio para todos los portafolios
     */
    public function actualizarPrecios(Request $request, Servicio $servicio): RedirectResponse
    {
        abort_if($servicio->empresa_id !== auth()->user()->empresa_id, 403);

        $empresaId = auth()->user()->empresa_id;

        $request->validate([
            'precios' => ['required', 'array'],
            'precios.*' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ], [
            'precios.*.numeric' => 'Los precios deben ser valores numéricos.',
            'precios.*.min' => 'Los precios no pueden ser negativos.',
            'precios.*.max' => 'Los precios no pueden exceder 99.999.999.',
        ]);

        foreach ($request->precios as $portafolioId => $precio) {
            if ($precio !== null && $precio !== '') {
                PrecioServicio::updateOrCreate(
                    [
                        'empresa_id' => $empresaId,
                        'servicio_id' => $servicio->id,
                        'portafolio_id' => $portafolioId,
                    ],
                    [
                        'precio' => $precio,
                    ]
                );
            } else {
                // Si el precio está vacío, eliminar el registro si existe
                PrecioServicio::where('empresa_id', $empresaId)
                    ->where('servicio_id', $servicio->id)
                    ->where('portafolio_id', $portafolioId)
                    ->delete();
            }
        }

        return redirect()->route('admin.servicios.index')
            ->with('exito', 'Precios del servicio "' . $servicio->nombre . '" actualizados correctamente.');
    }

    /**
     * Vista consolidada de todos los precios (matriz servicios x portafolios)
     */
    public function matrizPrecios(): View
    {
        $empresaId = auth()->user()->empresa_id;

        $servicios = Servicio::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $portafolios = Portafolio::where('empresa_id', $empresaId)
            ->orderBy('nombre_convenio')
            ->get();

        // Obtener todos los precios y organizarlos en matriz
        $precios = PrecioServicio::where('empresa_id', $empresaId)
            ->get()
            ->groupBy('servicio_id')
            ->map(function ($group) {
                return $group->keyBy('portafolio_id');
            });

        return view('admin.precios.matriz', compact('servicios', 'portafolios', 'precios'));
    }
}
