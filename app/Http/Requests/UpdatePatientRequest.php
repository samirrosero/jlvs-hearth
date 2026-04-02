<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $empresaId  = auth()->user()->empresa_id;
        $pacienteId = $this->route('paciente')?->id ?? $this->route('paciente');
        return [
            'nombre_completo'  => ['sometimes', 'string', 'max:255'],
            'fecha_nacimiento' => ['sometimes', 'date', 'before:today'],
            'sexo'             => ['sometimes', 'in:M,F,Otro'],
            'identificacion'   => ['sometimes', 'string', 'max:20', Rule::unique('pacientes')->where('empresa_id', $empresaId)->ignore($pacienteId)],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'correo'           => ['nullable', 'email', 'max:255'],
            'direccion'        => ['nullable', 'string', 'max:500'],
        ];
    }
}
