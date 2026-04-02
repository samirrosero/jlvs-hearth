<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentExecutionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'inicio_atencion'  => ['sometimes', 'date'],
            'fin_atencion'     => ['nullable', 'date', 'after:inicio_atencion'],
            'duracion_minutos' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
