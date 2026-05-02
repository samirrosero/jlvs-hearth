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
            ->when(request('buscar'), fn ($q) => $q->where('nombre_completo', 'like', '%' . request('buscar') . '%'))
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
        $data = $request->validate([
            'nombre_completo'  => ['required', 'string', 'max:200'],
            'identificacion'   => ['required', 'string', 'max:20'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo'             => ['nullable', 'in:M,F,Otro'],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'correo'           => ['nullable', 'email'],
            'direccion'        => ['nullable', 'string'],
        ]);

        Paciente::create(array_merge($data, [
            'empresa_id' => auth()->user()->empresa_id,
        ]));

        return redirect()->route('gestor.pacientes')->with('exito', 'Paciente registrado correctamente.');
    }

    public function buscar(Request $request)
    {
        $request->validate(['identificacion' => ['required', 'string', 'max:30']]);

        $paciente = Paciente::where('empresa_id', auth()->user()->empresa_id)
            ->where('identificacion', $request->input('identificacion'))
            ->first(['id', 'nombre_completo', 'identificacion', 'correo', 'telefono']);

        if (! $paciente) {
            return response()->json(['encontrado' => false, 'paciente' => null]);
        }

        return response()->json(['encontrado' => true, 'paciente' => $paciente]);
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