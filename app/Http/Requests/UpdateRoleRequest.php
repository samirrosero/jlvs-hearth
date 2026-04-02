<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $rolId = $this->route('rol')?->id ?? $this->route('rol');
        return [
            'nombre'      => ['sometimes', 'string', 'max:100', Rule::unique('roles', 'nombre')->ignore($rolId)],
            'descripcion' => ['nullable', 'string'],
        ];
    }
}
