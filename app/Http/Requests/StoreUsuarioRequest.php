<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'         => ['required', 'string', 'max:150'],
            'email'          => ['required', 'email', 'max:255', 'unique:users,email'],
            'identificacion' => ['required', 'string', 'max:20', 'unique:users,identificacion'],
            'password'       => ['required', 'string', 'min:8', 'confirmed'],
            // Solo puede asignar roles internos — no puede crear admins ni pacientes
            'rol_id'         => ['required', Rule::exists('roles', 'id')->whereIn('nombre', ['medico', 'gestor_citas'])],
        ];
    }
}
