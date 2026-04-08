<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreValoracionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $pacienteId = auth()->user()->paciente?->id;

        return [
            // La cita debe pertenecer al paciente autenticado y no tener valoración aún
            'cita_id'    => [
                'required',
                Rule::exists('citas', 'id')->where('paciente_id', $pacienteId),
                'unique:valoraciones,cita_id',
            ],
            'puntuacion' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
