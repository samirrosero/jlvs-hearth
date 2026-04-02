<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHorarioMedicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;

        return [
            'medico_id'    => ['sometimes', Rule::exists('medicos', 'id')->where('empresa_id', $empresaId)],
            'dia_semana'   => ['sometimes', 'integer', 'min:0', 'max:6'],
            'hora_inicio'  => ['sometimes', 'date_format:H:i'],
            'hora_fin'     => ['sometimes', 'date_format:H:i', 'after:hora_inicio'],
            'activo'       => ['sometimes', 'boolean'],
        ];
    }
}
