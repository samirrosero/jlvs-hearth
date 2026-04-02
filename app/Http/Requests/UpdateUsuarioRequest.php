<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $usuarioId = $this->route('usuario');

        return [
            'nombre'         => ['sometimes', 'string', 'max:150'],
            'email'          => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuarioId)],
            'identificacion' => ['sometimes', 'string', 'max:20', Rule::unique('users', 'identificacion')->ignore($usuarioId)],
            'password'       => ['sometimes', 'string', 'min:8', 'confirmed'],
        ];
    }
}
