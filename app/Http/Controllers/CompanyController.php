<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Rol;
use App\Models\User;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Empresa::orderBy('nombre')->get());
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $datos = $request->validated();

        // Crear empresa y su administrador en una sola transacción
        $resultado = DB::transaction(function () use ($datos) {
            $empresa = Empresa::create([
                'nit'       => $datos['nit'],
                'nombre'    => $datos['nombre'],
                'telefono'  => $datos['telefono'] ?? null,
                'correo'    => $datos['correo'] ?? null,
                'direccion' => $datos['direccion'] ?? null,
                'ciudad'    => $datos['ciudad'] ?? null,
                'activo'    => true,
            ]);

            $rolAdmin = Rol::where('nombre', 'administrador')->firstOrFail();

            $admin = User::create([
                'empresa_id'     => $empresa->id,
                'rol_id'         => $rolAdmin->id,
                'nombre'         => $datos['admin_nombre'],
                'email'          => $datos['admin_email'],
                'identificacion' => $datos['admin_identificacion'],
                'password'       => Hash::make($datos['admin_password']),
            ]);

            return ['empresa' => $empresa, 'administrador' => $admin->only('id', 'nombre', 'email')];
        });

        return response()->json($resultado, 201);
    }

    public function show(Empresa $empresa): JsonResponse
    {
        $this->authorize('view', $empresa);
        return response()->json($empresa->load('usuarios', 'portafolios'));
    }

    public function update(UpdateCompanyRequest $request, Empresa $empresa): JsonResponse
    {
        $this->authorize('update', $empresa);
        $empresa->update($request->validated());
        return response()->json($empresa);
    }

    public function destroy(Empresa $empresa): JsonResponse
    {
        $this->authorize('delete', $empresa);
        $empresa->update(['activo' => false]);
        return response()->json(['message' => 'Empresa desactivada.']);
    }
}
