<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistroPacienteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Credenciales de acceso al sistema
            'email'                  => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'               => ['required', 'string', 'min:8', 'confirmed'],

            // Datos personales del paciente
            'nombre_completo'        => ['required', 'string', 'max:150'],
            'identificacion'         => ['required', 'string', 'max:20'],
            'fecha_nacimiento'       => ['required', 'date', 'before:today'],
            'sexo'                   => ['required', Rule::in(['M', 'F', 'Otro'])],
            'telefono'               => ['required', 'string', 'max:20'],
            'direccion'              => ['nullable', 'string', 'max:255'],

            // La IPS a la que pertenece el paciente (requerida para multi-tenant)
            'empresa_id'             => ['required', 'exists:empresas,id'],
        ];
    }
}
