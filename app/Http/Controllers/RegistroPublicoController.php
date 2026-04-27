<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Paciente;
use App\Models\Portafolio;
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

        $portafolios = $empresa
            ? Portafolio::where('empresa_id', $empresa->id)->orderBy('nombre_convenio')->get()
            : collect();

        return view('auth.registro', compact('empresa', 'portafolios'));
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
            'fecha_nacimiento'  => ['required', 'date', 'before:today'],
            'sexo'              => ['required', 'in:M,F,Otro'],
            'telefono'          => ['required', 'string', 'max:20'],
            'correo'            => ['required', 'email', 'max:150', 'confirmed'],
            'correo_confirmation' => ['required'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
            'portafolio_id'     => ['required', 'exists:portafolios,id'],
            'nombre_aseguradora'=> ['nullable', 'string', 'max:100'],
            'numero_poliza'     => ['nullable', 'string', 'max:60'],
        ], [
            'portafolio_id.required' => 'Selecciona tu tipo de cobertura.',
            'portafolio_id.exists'   => 'La cobertura seleccionada no es válida.',
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
                'usuario_id'        => $usuario->id,
                'empresa_id'        => $empresa->id,
                'portafolio_id'     => $request->portafolio_id,
                'nombre_completo'   => trim($request->nombres . ' ' . $request->apellidos),
                'tipo_documento'    => $request->tipo_documento,
                'identificacion'    => $request->numero_documento,
                'fecha_nacimiento'  => $request->fecha_nacimiento,
                'sexo'              => $request->sexo,
                'telefono'          => $request->telefono,
                'correo'            => $request->correo,
                'nombre_aseguradora'=> $request->nombre_aseguradora,
                'numero_poliza'     => $request->numero_poliza,
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
            // Campos específicos por rol (foto_diploma opcional por ahora para facilitar pruebas)
            'especialidad'      => ['required_if:rol_solicitado,medico', 'nullable', 'string', 'max:100'],
            'numero_tarjeta_profesional' => ['required_if:rol_solicitado,medico', 'nullable', 'string', 'max:50'],
            'foto_diploma'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'documento_acreditacion' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
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

        // Guardar fotos de documentos
        $fotoPath = null;
        if ($request->hasFile('foto_documento')) {
            $fotoPath = $request->file('foto_documento')
                ->store("solicitudes/{$empresa->id}/documentos", 'public');
        }

        $fotoDiplomaPath = null;
        if ($request->hasFile('foto_diploma')) {
            $fotoDiplomaPath = $request->file('foto_diploma')
                ->store("solicitudes/{$empresa->id}/diplomas", 'public');
        }

        $docAcreditacionPath = null;
        if ($request->hasFile('documento_acreditacion')) {
            $docAcreditacionPath = $request->file('documento_acreditacion')
                ->store("solicitudes/{$empresa->id}/acreditaciones", 'public');
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
            'especialidad'       => $request->especialidad,
            'numero_tarjeta_profesional' => $request->numero_tarjeta_profesional,
            'departamento'       => $request->departamento,
            'municipio'          => $request->municipio,
            'foto_documento_path' => $fotoPath,
            'foto_diploma_path' => $fotoDiplomaPath,
            'documento_acreditacion_path' => $docAcreditacionPath,
            'estado'             => 'pendiente',
        ]);

        // Redirigir al login manteniendo el parámetro de empresa
        $loginParams = [];
        if ($empresa) {
            $loginParams['empresa'] = $empresa->id;
            session(['login_empresa_id' => $empresa->id]);
        }

        return redirect()->route('login', $loginParams)
            ->with('exito', 'Solicitud enviada. El administrador la revisará y te notificará por correo cuando sea aprobada.');
    }
}
