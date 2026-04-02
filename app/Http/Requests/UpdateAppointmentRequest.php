<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;
        return [
            'medico_id'     => ['sometimes', Rule::exists('medicos', 'id')->where('empresa_id', $empresaId)],
            'paciente_id'   => ['sometimes', Rule::exists('pacientes', 'id')->where('empresa_id', $empresaId)],
            'estado_id'     => ['sometimes', 'exists:estados_cita,id'],
            'modalidad_id'  => ['sometimes', 'exists:modalidades_cita,id'],
            'portafolio_id' => ['nullable', Rule::exists('portafolios', 'id')->where('empresa_id', $empresaId)],
            'fecha'         => ['sometimes', 'date'],
            'hora'          => ['sometimes', 'date_format:H:i'],
            'activo'        => ['boolean'],
        ];
    }
}
