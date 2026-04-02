<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClinicalHistoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'motivo_consulta'   => ['sometimes', 'string'],
            'enfermedad_actual' => ['sometimes', 'string'],
            'antecedentes'      => ['nullable', 'array'],
            'diagnostico'       => ['sometimes', 'string'],
            'plan_tratamiento'  => ['sometimes', 'string'],
            'evaluacion'        => ['nullable', 'string'],
            'observaciones'     => ['nullable', 'string'],
        ];
    }
}
