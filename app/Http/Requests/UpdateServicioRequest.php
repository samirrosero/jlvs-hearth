<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;
        $servicioId = $this->route('servicio');

        return [
            'nombre'            => ['sometimes', 'string', 'max:150',
                Rule::unique('servicios')->where('empresa_id', $empresaId)->ignore($servicioId),
            ],
            'descripcion'       => ['nullable', 'string'],
            'duracion_minutos'  => ['sometimes', 'integer', 'min:5', 'max:480'],
            'activo'            => ['sometimes', 'boolean'],
        ];
    }
}
