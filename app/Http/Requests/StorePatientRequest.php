<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;
        return [
            'nombre_completo'  => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'sexo'             => ['required', 'in:M,F,Otro'],
            'identificacion'   => ['required', 'string', 'max:20', Rule::unique('pacientes')->where('empresa_id', $empresaId)],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'correo'           => ['nullable', 'email', 'max:255'],
            'direccion'        => ['nullable', 'string', 'max:500'],
            'usuario_id'       => ['nullable', 'exists:users,id'],
        ];
    }
}
