<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSignosVitalesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;

        return [
            'ejecucion_cita_id'       => ['required',
                // Rule::exists solo soporta ->where(), no ->whereHas()
                // La validación de tenant se hace mediante join a la tabla citas
                Rule::exists('ejecuciones_cita', 'id')->where(function ($query) use ($empresaId) {
                    $query->whereIn('cita_id', function ($sub) use ($empresaId) {
                        $sub->select('id')->from('citas')->where('empresa_id', $empresaId);
                    });
                }),
                Rule::unique('signos_vitales', 'ejecucion_cita_id'),
            ],
            'paciente_id'             => ['required', Rule::exists('pacientes', 'id')->where('empresa_id', $empresaId)],
            'peso_kg'                 => ['nullable', 'numeric', 'min:0.5', 'max:500'],
            'talla_cm'                => ['nullable', 'numeric', 'min:20', 'max:300'],
            'presion_sistolica'       => ['nullable', 'integer', 'min:50', 'max:300'],
            'presion_diastolica'      => ['nullable', 'integer', 'min:20', 'max:200'],
            'temperatura_c'           => ['nullable', 'numeric', 'min:30', 'max:45'],
            'frecuencia_cardiaca'     => ['nullable', 'integer', 'min:20', 'max:300'],
            'saturacion_oxigeno'      => ['nullable', 'integer', 'min:50', 'max:100'],
            'frecuencia_respiratoria' => ['nullable', 'integer', 'min:5', 'max:60'],
            'observaciones'           => ['nullable', 'string'],
        ];
    }
}
