<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Lista todos los usuarios (internos) de la empresa del admin autenticado.
     * Excluye pacientes — ellos se gestionan desde /pacientes.
     */
    public function index(): JsonResponse
    {
        $usuarios = User::where('empresa_id', auth()->user()->empresa_id)
            ->whereHas('rol', fn ($q) => $q->whereIn('nombre', ['administrador', 'medico', 'gestor_citas']))
            ->with('rol')
            ->get();

        return response()->json($usuarios);
    }

    /**
     * Crea un usuario interno (medico o gestor_citas).
     * El admin no puede crear otros admins ni pacientes desde aquí.
     */
    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $datos = $request->validated();

        $usuario = User::create([
            'empresa_id'     => auth()->user()->empresa_id,
            'rol_id'         => $datos['rol_id'],
            'nombre'         => $datos['nombre'],
            'email'          => $datos['email'],
            'identificacion' => $datos['identificacion'],
            'password'       => Hash::make($datos['password']),
        ]);

        return response()->json($usuario->load('rol'), 201);
    }

    /**
     * Ver un usuario de la misma empresa.
     */
    public function show(User $usuario): JsonResponse
    {
        abort_if($usuario->empresa_id !== auth()->user()->empresa_id, 403);

        return response()->json($usuario->load('rol'));
    }

    /**
     * Actualizar nombre, email, identificación o contraseña de un usuario.
     */
    public function update(UpdateUsuarioRequest $request, User $usuario): JsonResponse
    {
        abort_if($usuario->empresa_id !== auth()->user()->empresa_id, 403);

        $datos = $request->validated();

        if (isset($datos['password'])) {
            $datos['password'] = Hash::make($datos['password']);
        }

        $usuario->update($datos);

        return response()->json($usuario->load('rol'));
    }

    /**
     * Desactiva el usuario (no lo elimina físicamente).
     */
    public function destroy(User $usuario): JsonResponse
    {
        abort_if($usuario->empresa_id !== auth()->user()->empresa_id, 403);
        abort_if($usuario->id === auth()->id(), 422, 'No puedes desactivar tu propia cuenta.');

        // Soft delete lógico — preserva el historial de citas e historias
        $usuario->update(['activo' => false]);

        return response()->json(['message' => 'Usuario desactivado.']);
    }
}
