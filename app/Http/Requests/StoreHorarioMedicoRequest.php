<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHorarioMedicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;

        return [
            'medico_id'    => ['required', Rule::exists('medicos', 'id')->where('empresa_id', $empresaId)],
            'dia_semana'   => ['required', 'integer', 'min:0', 'max:6'],
            'hora_inicio'  => ['required', 'date_format:H:i'],
            'hora_fin'     => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'activo'       => ['nullable', 'boolean'],
        ];
    }
}
