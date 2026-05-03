<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GestorPacientesController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::where('empresa_id', auth()->user()->empresa_id)
            ->when(request('buscar'), fn ($q) => $q->where(function ($q) {
                $q->where('nombre_completo', 'like', '%' . request('buscar') . '%')
                  ->orWhere('identificacion', 'like', '%' . request('buscar') . '%');
            }))
            ->orderBy('nombre_completo')
            ->paginate(15)
            ->withQueryString();

        return view('gestor.pacientes.index', compact('pacientes'));
    }

    public function create()
    {
        return view('gestor.pacientes.create');
    }

    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'tipo_documento'   => ['required', 'string', 'max:10'],
            'identificacion'   => ['required', 'string', 'max:20',
                Rule::unique('pacientes')->where('empresa_id', $empresaId)],
            'nombres'          => ['required', 'string', 'max:100'],
            'apellidos'        => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:today'],
            'sexo'             => ['required', 'in:M,F,Otro'],
            'telefono'         => ['required', 'string', 'max:20'],
            'correo'           => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'         => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'tipo_documento.required'      => 'El tipo de documento es obligatorio.',
            'identificacion.required'      => 'El número de documento es obligatorio.',
            'identificacion.unique'        => 'Ya existe un paciente con ese número de documento.',
            'nombres.required'             => 'Los nombres son obligatorios.',
            'apellidos.required'           => 'Los apellidos son obligatorios.',
            'fecha_nacimiento.required'    => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.date'        => 'La fecha de nacimiento no es válida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser una fecha futura.',
            'sexo.required'                => 'El sexo es obligatorio.',
            'sexo.in'                      => 'El sexo debe ser Masculino, Femenino u Otro.',
            'telefono.required'            => 'El teléfono es obligatorio.',
            'correo.required'              => 'El correo electrónico es obligatorio.',
            'correo.email'                 => 'El correo electrónico no es válido.',
            'correo.unique'                => 'Este correo ya está registrado en el sistema.',
            'password.required'            => 'La contraseña es obligatoria.',
            'password.min'                 => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed'           => 'Las contraseñas no coinciden.',
        ]);

        return DB::transaction(function () use ($data, $empresaId) {
            $rolPaciente = Rol::where('nombre', 'paciente')->firstOrFail();

            $usuario = User::create([
                'empresa_id'            => $empresaId,
                'rol_id'                => $rolPaciente->id,
                'nombre'                => trim($data['nombres'] . ' ' . $data['apellidos']),
                'email'                 => $data['correo'],
                'identificacion'        => $data['identificacion'],
                'password'              => $data['password'],
                'activo'                => true,
                'debe_cambiar_password' => true,
            ]);

            Paciente::create([
                'usuario_id'       => $usuario->id,
                'empresa_id'       => $empresaId,
                'tipo_documento'   => $data['tipo_documento'],
                'nombre_completo'  => trim($data['nombres'] . ' ' . $data['apellidos']),
                'identificacion'   => $data['identificacion'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'sexo'             => $data['sexo'],
                'telefono'         => $data['telefono'],
                'correo'           => $data['correo'],
            ]);

            return redirect()->route('gestor.pacientes')->with('exito', 'Paciente registrado correctamente.');
        });
    }

    public function buscar(Request $request)
    {
        $request->validate(['identificacion' => ['required', 'string', 'max:30']]);

        $paciente = Paciente::where('empresa_id', auth()->user()->empresa_id)
            ->where('identificacion', $request->input('identificacion'))
            ->with('portafolio')
            ->first(['id', 'nombre_completo', 'identificacion', 'correo', 'telefono', 'portafolio_id']);

        if (! $paciente) {
            return response()->json(['encontrado' => false, 'paciente' => null]);
        }

        return response()->json([
            'encontrado' => true,
            'paciente' => array_merge(
                $paciente->toArray(),
                ['portafolio' => $paciente->portafolio]
            )
        ]);
    }

    public function registroRapido(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'nombre_completo'  => ['required', 'string', 'max:150'],
            'identificacion'   => ['required', 'string', 'max:20',
                Rule::unique('pacientes')->where('empresa_id', $empresaId)],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'sexo'             => ['required', 'in:M,F,Otro'],
            'telefono'         => ['required', 'string', 'max:20'],
            'email_cuenta'     => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        return DB::transaction(function () use ($data, $empresaId) {
            $rolPaciente  = Rol::where('nombre', 'paciente')->firstOrFail();
            $passwordTemp = strtoupper(Str::random(4)) . '-' . rand(1000, 9999);

            $usuario = User::create([
                'empresa_id'            => $empresaId,
                'rol_id'                => $rolPaciente->id,
                'nombre'                => $data['nombre_completo'],
                'email'                 => $data['email_cuenta'],
                'identificacion'        => $data['identificacion'],
                'password'              => $passwordTemp,
                'activo'                => true,
                'debe_cambiar_password' => true,
            ]);

            $paciente = Paciente::create([
                'usuario_id'       => $usuario->id,
                'empresa_id'       => $empresaId,
                'nombre_completo'  => $data['nombre_completo'],
                'identificacion'   => $data['identificacion'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'sexo'             => $data['sexo'],
                'telefono'         => $data['telefono'],
                'correo'           => $data['email_cuenta'],
            ]);

            return response()->json([
                'paciente'          => $paciente->only(['id', 'nombre_completo', 'identificacion', 'correo']),
                'password_temporal' => $passwordTemp,
            ], 201);
        });
    }
}
