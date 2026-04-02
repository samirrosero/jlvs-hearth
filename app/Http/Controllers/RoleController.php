<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Rol::orderBy('nombre')->get());
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $rol = Rol::create($request->validated());
        return response()->json($rol, 201);
    }

    public function show(Rol $rol): JsonResponse
    {
        return response()->json($rol);
    }

    public function update(UpdateRoleRequest $request, Rol $rol): JsonResponse
    {
        $rol->update($request->validated());
        return response()->json($rol);
    }

    public function destroy(Rol $rol): JsonResponse
    {
        $rol->delete();
        return response()->json(null, 204);
    }
}
