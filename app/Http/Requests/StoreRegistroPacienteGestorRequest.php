<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistroPacienteGestorRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;

        return [
            // Datos personales del paciente
            'nombre_completo'  => ['required', 'string', 'max:150'],
            'identificacion'   => ['required', 'string', 'max:20', Rule::unique('pacientes')->where('empresa_id', $empresaId)],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'sexo'             => ['required', Rule::in(['M', 'F', 'Otro'])],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'correo'           => ['nullable', 'email', 'max:255'],
            'direccion'        => ['nullable', 'string', 'max:500'],

            // Si se envía 'crear_cuenta' = true, el gestor crea credenciales de acceso
            'crear_cuenta'     => ['boolean'],
            'email_cuenta'     => ['required_if:crear_cuenta,true', 'nullable', 'email', 'max:255', 'unique:users,email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email_cuenta.required_if' => 'El correo de la cuenta es obligatorio cuando se crea una cuenta de acceso.',
            'email_cuenta.unique'       => 'Ya existe un usuario con ese correo electrónico.',
        ];
    }
}
