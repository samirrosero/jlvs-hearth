<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAntecedentesPacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo'         => ['sometimes', Rule::in(['personal', 'familiar', 'quirurgico', 'alergico', 'farmacologico', 'otros'])],
            'descripcion'  => ['sometimes', 'string'],
            'activo'       => ['sometimes', 'boolean'],
        ];
    }
}
