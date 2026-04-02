<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;

        return [
            'nombre'            => ['required', 'string', 'max:150',
                Rule::unique('servicios')->where('empresa_id', $empresaId),
            ],
            'descripcion'       => ['nullable', 'string'],
            'duracion_minutos'  => ['nullable', 'integer', 'min:5', 'max:480'],
            'activo'            => ['nullable', 'boolean'],
        ];
    }
}
