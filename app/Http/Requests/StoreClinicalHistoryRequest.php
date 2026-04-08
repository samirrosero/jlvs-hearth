<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClinicalHistoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;
        return [
            'ejecucion_cita_id' => ['required', 'exists:ejecuciones_cita,id', 'unique:historias_clinicas,ejecucion_cita_id'],
            'paciente_id'       => ['required', Rule::exists('pacientes', 'id')->where('empresa_id', $empresaId)],
            'motivo_consulta'   => ['required', 'string'],
            'enfermedad_actual' => ['required', 'string'],
            'antecedentes'      => ['nullable', 'array'],
            'diagnostico'       => ['required', 'string'],
            'codigo_cie10'      => ['nullable', 'string', 'max:10', 'exists:cie10,codigo'],
            'descripcion_cie10' => ['nullable', 'string', 'max:255'],
            'plan_tratamiento'  => ['required', 'string'],
            'evaluacion'        => ['nullable', 'string'],
            'observaciones'     => ['nullable', 'string'],
        ];
    }
}
