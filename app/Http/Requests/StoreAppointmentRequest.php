<?php

namespace App\Http\Requests;

use App\Rules\MedicoDisponible;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;
        return [
            'medico_id'     => ['required', Rule::exists('medicos', 'id')->where('empresa_id', $empresaId), new MedicoDisponible()],
            'paciente_id'   => ['required', Rule::exists('pacientes', 'id')->where('empresa_id', $empresaId)],
            'estado_id'     => ['required', 'exists:estados_cita,id'],
            'modalidad_id'  => ['required', 'exists:modalidades_cita,id'],
            'portafolio_id' => ['nullable', Rule::exists('portafolios', 'id')->where('empresa_id', $empresaId)],
            'servicio_id'   => ['nullable', Rule::exists('servicios', 'id')->where('empresa_id', $empresaId)],
            'fecha'         => ['required', 'date', 'after_or_equal:today'],
            'hora'          => ['required', 'date_format:H:i'],
            'activo'        => ['boolean'],
        ];
    }
}
