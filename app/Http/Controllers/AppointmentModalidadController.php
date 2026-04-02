<?php

namespace App\Http\Controllers;

use App\Models\ModalidadCita;
use App\Http\Requests\StoreAppointmentModalidadRequest;
use App\Http\Requests\UpdateAppointmentModalidadRequest;
use Illuminate\Http\JsonResponse;

class AppointmentModalidadController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ModalidadCita::orderBy('nombre')->get());
    }

    public function store(StoreAppointmentModalidadRequest $request): JsonResponse
    {
        $modalidad = ModalidadCita::create($request->validated());
        return response()->json($modalidad, 201);
    }

    public function show(ModalidadCita $modalidad): JsonResponse
    {
        return response()->json($modalidad);
    }

    public function update(UpdateAppointmentModalidadRequest $request, ModalidadCita $modalidad): JsonResponse
    {
        $modalidad->update($request->validated());
        return response()->json($modalidad);
    }

    public function destroy(ModalidadCita $modalidad): JsonResponse
    {
        $modalidad->delete();
        return response()->json(null, 204);
    }
}
