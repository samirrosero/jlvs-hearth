<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalPrescriptionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'medicamentos' => ['sometimes', 'string'],
            'indicaciones' => ['sometimes', 'string'],
        ];
    }
}
