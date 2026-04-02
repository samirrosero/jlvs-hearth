<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalPrescriptionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'historia_clinica_id' => ['required', 'exists:historias_clinicas,id'],
            'medicamentos'        => ['required', 'string'],
            'indicaciones'        => ['required', 'string'],
        ];
    }
}
