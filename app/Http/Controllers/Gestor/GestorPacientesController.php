<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;

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
}