<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OnboardingController extends Controller
{
    public function show()
    {
        return view('onboarding.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'               => ['required', 'string', 'max:255'],
            'nit'                  => ['required', 'string', 'max:20', 'unique:empresas,nit'],
            'ciudad'               => ['nullable', 'string', 'max:100'],
            'telefono'             => ['nullable', 'string', 'max:20'],
            'correo'               => ['nullable', 'email', 'max:255'],

            'admin_nombre'         => ['required', 'string', 'max:150'],
            'admin_tipo_documento' => ['required', 'string', 'in:CC,TI,CE,PP,NUIP,RC'],
            'admin_identificacion' => ['required', 'string', 'max:20', 'unique:users,identificacion'],
            'admin_email'          => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password'       => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'nit.unique'                  => 'Ya existe una empresa registrada con ese NIT.',
            'admin_identificacion.unique' => 'Ya existe un usuario con ese número de documento.',
            'admin_email.unique'          => 'Ya existe un usuario con ese correo electrónico.',
        ]);

        $empresaId = null;

        DB::transaction(function () use ($request, &$empresaId) {
            $empresa = Empresa::create([
                'nit'      => $request->nit,
                'nombre'   => $request->nombre,
                'ciudad'   => $request->ciudad,
                'telefono' => $request->telefono,
                'correo'   => $request->correo,
                'activo'   => true,
            ]);

            $empresaId = $empresa->id;

            $rolAdmin = Rol::where('nombre', 'administrador')->firstOrFail();

            User::create([
                'empresa_id'     => $empresa->id,
                'rol_id'         => $rolAdmin->id,
                'nombre'         => $request->admin_nombre,
                'email'          => $request->admin_email,
                'tipo_documento' => $request->admin_tipo_documento,
                'identificacion' => $request->admin_identificacion,
                'password'       => Hash::make($request->admin_password),
                'activo'         => true,
            ]);
        });

        // Redirigir al login con el parámetro de empresa para mostrar su branding
        return redirect()->route('login', ['empresa' => $empresaId])
            ->with('exito', '¡Tu IPS fue registrada! Ya puedes iniciar sesión con tus credenciales.');
    }
}
