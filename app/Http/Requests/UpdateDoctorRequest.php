<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $medicoId = $this->route('medico')?->id ?? $this->route('medico');
        return [
            'especialidad'    => ['sometimes', 'string', 'max:255'],
            'registro_medico' => ['sometimes', 'string', 'max:100', Rule::unique('medicos', 'registro_medico')->ignore($medicoId)],
        ];
    }
}
