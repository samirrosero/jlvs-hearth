<?php

namespace App\Http\Controllers;

use App\Models\EstadoCita;
use App\Http\Requests\StoreAppointmentStatusRequest;
use App\Http\Requests\UpdateAppointmentStatusRequest;
use Illuminate\Http\JsonResponse;

class AppointmentStatusController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(EstadoCita::orderBy('nombre')->get());
    }

    public function store(StoreAppointmentStatusRequest $request): JsonResponse
    {
        $estado = EstadoCita::create($request->validated());
        return response()->json($estado, 201);
    }

    public function show(EstadoCita $estado): JsonResponse
    {
        return response()->json($estado);
    }

    public function update(UpdateAppointmentStatusRequest $request, EstadoCita $estado): JsonResponse
    {
        $estado->update($request->validated());
        return response()->json($estado);
    }

    public function destroy(EstadoCita $estado): JsonResponse
    {
        $estado->delete();
        return response()->json(null, 204);
    }
}
