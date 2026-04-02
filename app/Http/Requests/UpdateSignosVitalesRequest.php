<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSignosVitalesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'peso_kg'                 => ['sometimes', 'numeric', 'min:0.5', 'max:500'],
            'talla_cm'                => ['sometimes', 'numeric', 'min:20', 'max:300'],
            'presion_sistolica'       => ['sometimes', 'integer', 'min:50', 'max:300'],
            'presion_diastolica'      => ['sometimes', 'integer', 'min:20', 'max:200'],
            'temperatura_c'           => ['sometimes', 'numeric', 'min:30', 'max:45'],
            'frecuencia_cardiaca'     => ['sometimes', 'integer', 'min:20', 'max:300'],
            'saturacion_oxigeno'      => ['sometimes', 'integer', 'min:50', 'max:100'],
            'frecuencia_respiratoria' => ['sometimes', 'integer', 'min:5', 'max:60'],
            'observaciones'           => ['nullable', 'string'],
        ];
    }
}
