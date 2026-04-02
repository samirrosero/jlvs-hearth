<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Rol;
use App\Models\User;
use App\Http\Requests\RegistroPacienteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegistroPacienteController extends Controller
{
    /**
     * Registro público de paciente.
     * Crea el usuario (credenciales) y el perfil de paciente en una sola transacción.
     * La identificación debe ser única dentro de la empresa indicada.
     */
    public function store(RegistroPacienteRequest $request): JsonResponse
    {
        $datos = $request->validated();

        // Verificar que la identificación no esté ya registrada en esa empresa
        $existePaciente = Paciente::where('identificacion', $datos['identificacion'])
            ->where('empresa_id', $datos['empresa_id'])
            ->exists();

        if ($existePaciente) {
            throw ValidationException::withMessages([
                'identificacion' => ['Ya existe un paciente con esa identificación en esta IPS.'],
            ]);
        }

        $resultado = DB::transaction(function () use ($datos) {
            $rolPaciente = Rol::where('nombre', 'paciente')->firstOrFail();

            $usuario = User::create([
                'empresa_id'     => $datos['empresa_id'],
                'rol_id'         => $rolPaciente->id,
                'nombre'         => $datos['nombre_completo'],
                'email'          => $datos['email'],
                'identificacion' => $datos['identificacion'],
                'password'       => Hash::make($datos['password']),
            ]);

            $paciente = Paciente::create([
                'usuario_id'      => $usuario->id,
                'empresa_id'      => $datos['empresa_id'],
                'nombre_completo' => $datos['nombre_completo'],
                'fecha_nacimiento' => $datos['fecha_nacimiento'],
                'sexo'            => $datos['sexo'],
                'telefono'        => $datos['telefono'],
                'correo'          => $datos['email'],
                'direccion'       => $datos['direccion'] ?? null,
                'identificacion'  => $datos['identificacion'],
            ]);

            return $paciente->load('usuario');
        });

        return response()->json([
            'message'  => 'Registro exitoso. Ya puedes iniciar sesión.',
            'paciente' => $resultado,
        ], 201);
    }
}
