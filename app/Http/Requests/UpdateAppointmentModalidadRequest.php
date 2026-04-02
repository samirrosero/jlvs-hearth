<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentModalidadRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('modalidad')?->id ?? $this->route('modalidad');
        return [
            'nombre' => ['sometimes', 'string', 'max:100', Rule::unique('modalidades_cita', 'nombre')->ignore($id)],
        ];
    }
}
