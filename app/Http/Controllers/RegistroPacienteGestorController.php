<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Rol;
use App\Models\User;
use App\Http\Requests\StoreRegistroPacienteGestorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistroPacienteGestorController extends Controller
{
    /**
     * El gestor de citas registra un paciente presencialmente.
     *
     * Casos:
     *   A) Solo perfil de paciente (sin cuenta) — igual que POST /pacientes
     *   B) Perfil de paciente + cuenta con contraseña temporal generada
     *      automáticamente. El flag 'debe_cambiar_password' queda en true
     *      para que el frontend fuerce el cambio en el primer login.
     *
     * POST /pacientes/registro-gestor
     * Body: { nombre_completo, identificacion, fecha_nacimiento, sexo,
     *         telefono?, correo?, direccion?,
     *         crear_cuenta: true, email_cuenta: "..." }
     */
    public function store(StoreRegistroPacienteGestorRequest $request): JsonResponse
    {
        $datos     = $request->validated();
        $empresaId = auth()->user()->empresa_id;

        $resultado = DB::transaction(function () use ($datos, $empresaId) {
            $usuarioId         = null;
            $passwordTemporal  = null;

            if (!empty($datos['crear_cuenta'])) {
                $rolPaciente      = Rol::where('nombre', 'paciente')->firstOrFail();
                $passwordTemporal = strtoupper(Str::random(4)) . '-' . rand(1000, 9999);

                $usuario = User::create([
                    'empresa_id'            => $empresaId,
                    'rol_id'                => $rolPaciente->id,
                    'nombre'                => $datos['nombre_completo'],
                    'email'                 => $datos['email_cuenta'],
                    'identificacion'        => $datos['identificacion'],
                    'password'              => $passwordTemporal,   // el cast 'hashed' lo cifra
                    'activo'                => true,
                    'debe_cambiar_password' => true,
                ]);

                $usuarioId = $usuario->id;
            }

            $paciente = Paciente::create([
                'usuario_id'       => $usuarioId,
                'empresa_id'       => $empresaId,
                'nombre_completo'  => $datos['nombre_completo'],
                'identificacion'   => $datos['identificacion'],
                'fecha_nacimiento' => $datos['fecha_nacimiento'],
                'sexo'             => $datos['sexo'],
                'telefono'         => $datos['telefono'] ?? null,
                'correo'           => $datos['correo'] ?? $datos['email_cuenta'] ?? null,
                'direccion'        => $datos['direccion'] ?? null,
            ]);

            return [
                'paciente'          => $paciente->load('usuario'),
                'password_temporal' => $passwordTemporal,   // el gestor lo anota y se lo entrega al paciente
            ];
        });

        return response()->json([
            'message'           => $resultado['password_temporal']
                ? 'Paciente registrado con cuenta de acceso. Entrega la contraseña temporal al paciente.'
                : 'Paciente registrado sin cuenta de acceso.',
            'paciente'          => $resultado['paciente'],
            'password_temporal' => $resultado['password_temporal'],
        ], 201);
    }
}
