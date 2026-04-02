<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachedDocumentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'historia_clinica_id' => ['required', 'exists:historias_clinicas,id'],
            'archivo'             => ['required', 'file', 'max:10240'],
        ];
    }
}
