<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePortfolioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre_convenio' => ['required', 'string', 'max:255'],
            'descripcion'     => ['nullable', 'string'],
        ];
    }
}
