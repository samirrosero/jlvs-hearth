<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('empresa')?->id ?? $this->route('empresa');
        return [
            'nit'       => ['sometimes', 'string', 'max:20', Rule::unique('empresas', 'nit')->ignore($id)],
            'nombre'    => ['sometimes', 'string', 'max:255'],
            'telefono'  => ['nullable', 'string', 'max:20'],
            'correo'    => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'ciudad'    => ['nullable', 'string', 'max:100'],
            'activo'    => ['boolean'],
        ];
    }
}
