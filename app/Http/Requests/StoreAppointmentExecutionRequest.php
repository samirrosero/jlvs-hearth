<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentExecutionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;
        return [
            'cita_id'          => ['required', Rule::exists('citas', 'id')->where('empresa_id', $empresaId), 'unique:ejecuciones_cita,cita_id'],
            'inicio_atencion'  => ['required', 'date'],
            'fin_atencion'     => ['nullable', 'date', 'after:inicio_atencion'],
            'duracion_minutos' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
