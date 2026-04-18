<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Paciente;
use App\Models\Rol;
use App\Models\SolicitudEmpleador;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegistroPublicoController extends Controller
{
    private const TIPOS_DOC = ['CC', 'TI', 'CE', 'PP', 'NUIP', 'RC'];
    private const ROLES_EMPLEADOR = ['administrador', 'medico', 'gestor_citas'];

    // ── Mostrar formulario de registro ───────────────────────────────────
    public function show(Request $request): View
    {
        // Detectar empresa por parámetro (igual que el login)
        $empresa = $this->detectarEmpresa($request);

        // Guardar en sesión para mantener branding durante el registro
        if ($empresa) {
            session(['registro_empresa_id' => $empresa->id]);
        } else {
            $empresaId = session('registro_empresa_id');
            if ($empresaId) {
                $empresa = Empresa::where('id', $empresaId)->where('activo', true)->first();
            }
        }

        return view('auth.registro', compact('empresa'));
    }

    // ── Detectar empresa por parámetro URL ─────────────────────────────────
    private function detectarEmpresa(Request $request): ?Empresa
    {
        if ($request->filled('empresa')) {
            return Empresa::where('id', $request->input('empresa'))
                ->where('activo', true)
                ->first();
        } elseif ($request->filled('nit')) {
            return Empresa::where('nit', $request->input('nit'))
                ->where('activo', true)
                ->first();
        }
        return null;
    }

    // ── Registro de afiliado (paciente) ──────────────────────────────────
    public function registrarAfiliado(Request $request): RedirectResponse
    {
        $empresa = $this->detectarEmpresa($request);
        if (!$empresa) {
            $empresaId = session('registro_empresa_id');
            $empresa = $empresaId
                ? Empresa::where('id', $empresaId)->where('activo', true)->firstOrFail()
                : Empresa::where('activo', true)->firstOrFail();
        }

        $request->validate([
            'tipo_documento'    => ['required', 'in:' . implode(',', self::TIPOS_DOC)],
            'numero_documento'  => ['required', 'string', 'max:20'],
            'nombres'           => ['required', 'string', 'max:100'],
            'apellidos'         => ['required', 'string', 'max:100'],
            'correo'            => ['required', 'email', 'max:150', 'confirmed'],
            'correo_confirmation' => ['required'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        // Verificar que la identificación no exista ya
        if (Paciente::where('identificacion', $request->numero_documento)
                    ->where('empresa_id', $empresa->id)->exists()) {
            throw ValidationException::withMessages([
                'numero_documento' => ['Ya existe un paciente con ese número de documento en esta IPS.'],
            ]);
        }
        if (User::where('email', $request->correo)->where('empresa_id', $empresa->id)->exists()) {
            throw ValidationException::withMessages([
                'correo' => ['Ese correo ya está registrado.'],
            ]);
        }

        DB::transaction(function () use ($request, $empresa) {
            $rolPaciente = Rol::where('nombre', 'paciente')->firstOrFail();

            $usuario = User::create([
                'empresa_id'     => $empresa->id,
                'rol_id'         => $rolPaciente->id,
                'nombre'         => trim($request->nombres . ' ' . $request->apellidos),
                'email'          => $request->correo,
                'identificacion' => $request->numero_documento,
                'tipo_documento' => $request->tipo_documento,
                'password'       => Hash::make($request->password),
            ]);

            Paciente::create([
                'usuario_id'      => $usuario->id,
                'empresa_id'      => $empresa->id,
                'nombre_completo' => trim($request->nombres . ' ' . $request->apellidos),
                'apellidos'       => $request->apellidos,
                'tipo_documento'  => $request->tipo_documento,
                'identificacion'  => $request->numero_documento,
                'correo'          => $request->correo,
            ]);
        });

        // Redirigir al login manteniendo el parámetro de empresa
        $loginParams = [];
        if ($empresa) {
            $loginParams['empresa'] = $empresa->id;
            session(['login_empresa_id' => $empresa->id]);
        }

        return redirect()->route('login', $loginParams)
            ->with('exito', '¡Registro exitoso! Ya puedes iniciar sesión.');
    }

    // ── Registro de empleador (queda pendiente de aprobación) ────────────
    public function registrarEmpleador(Request $request): RedirectResponse
    {
        $empresa = $this->detectarEmpresa($request);
        if (!$empresa) {
            $empresaId = session('registro_empresa_id');
            $empresa = $empresaId
                ? Empresa::where('id', $empresaId)->where('activo', true)->firstOrFail()
                : Empresa::where('activo', true)->firstOrFail();
        }

        $request->validate([
            'tipo_documento'    => ['required', 'in:' . implode(',', self::TIPOS_DOC)],
            'numero_documento'  => ['required', 'string', 'max:20'],
            'rol_solicitado'    => ['required', 'in:' . implode(',', self::ROLES_EMPLEADOR)],
            'nombres'           => ['required', 'string', 'max:100'],
            'apellidos'         => ['required', 'string', 'max:100'],
            'departamento'      => ['nullable', 'string', 'max:100'],
            'municipio'         => ['nullable', 'string', 'max:100'],
            'correo'            => ['required', 'email', 'max:150', 'confirmed'],
            'correo_confirmation' => ['required'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
            'foto_documento'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        // Verificar duplicados
        if (SolicitudEmpleador::where('correo', $request->correo)
                               ->where('empresa_id', $empresa->id)
                               ->where('estado', 'pendiente')->exists()) {
            throw ValidationException::withMessages([
                'correo' => ['Ya hay una solicitud pendiente con ese correo.'],
            ]);
        }
        if (User::where('email', $request->correo)->where('empresa_id', $empresa->id)->exists()) {
            throw ValidationException::withMessages([
                'correo' => ['Ese correo ya está registrado en el sistema.'],
            ]);
        }

        $fotoPath = null;
        if ($request->hasFile('foto_documento')) {
            $fotoPath = $request->file('foto_documento')
                ->store("solicitudes/{$empresa->id}", 'public');
        }

        SolicitudEmpleador::create([
            'empresa_id'         => $empresa->id,
            'tipo_documento'     => $request->tipo_documento,
            'numero_documento'   => $request->numero_documento,
            'nombres'            => $request->nombres,
            'apellidos'          => $request->apellidos,
            'correo'             => $request->correo,
            'password'           => Hash::make($request->password),
            'rol_solicitado'     => $request->rol_solicitado,
            'departamento'       => $request->departamento,
            'municipio'          => $request->municipio,
            'foto_documento_path' => $fotoPath,
            'estado'             => 'pendiente',
        ]);

        // Redirigir al login manteniendo el parámetro de empresa
        $loginParams = [];
        if ($empresa) {
            $loginParams['empresa'] = $empresa->id;
            session(['login_empresa_id' => $empresa->id]);
        }

        return redirect()->route('login', $loginParams)
            ->with('exito', 'Solicitud enviada. El administrador la revisará y te notificará pronto.');
    }
}
