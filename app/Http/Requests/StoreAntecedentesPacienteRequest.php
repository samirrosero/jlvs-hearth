<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAntecedentesPacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;

        return [
            'paciente_id'  => ['required', Rule::exists('pacientes', 'id')->where('empresa_id', $empresaId)],
            'tipo'         => ['required', Rule::in(['personal', 'familiar', 'quirurgico', 'alergico', 'farmacologico', 'otros'])],
            'descripcion'  => ['required', 'string'],
            'activo'       => ['nullable', 'boolean'],
        ];
    }
}
