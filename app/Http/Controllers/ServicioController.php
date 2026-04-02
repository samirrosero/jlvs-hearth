<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use Illuminate\Http\JsonResponse;

class ServicioController extends Controller
{
    public function index(): JsonResponse
    {
        $servicios = Servicio::where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('nombre')
            ->get();

        return response()->json($servicios);
    }

    public function store(StoreServicioRequest $request): JsonResponse
    {
        $servicio = Servicio::create(
            array_merge($request->validated(), ['empresa_id' => auth()->user()->empresa_id])
        );

        return response()->json($servicio, 201);
    }

    public function show(Servicio $servicio): JsonResponse
    {
        $this->authorize('view', $servicio);

        return response()->json($servicio);
    }

    public function update(UpdateServicioRequest $request, Servicio $servicio): JsonResponse
    {
        $this->authorize('update', $servicio);
        $servicio->update($request->validated());

        return response()->json($servicio);
    }

    public function destroy(Servicio $servicio): JsonResponse
    {
        $this->authorize('delete', $servicio);
        $servicio->update(['activo' => false]);

        return response()->json(['message' => 'Servicio desactivado.']);
    }
}
