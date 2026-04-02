<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Datos de la empresa
            'nit'            => ['required', 'string', 'max:20', 'unique:empresas,nit'],
            'nombre'         => ['required', 'string', 'max:255'],
            'telefono'       => ['nullable', 'string', 'max:20'],
            'correo'         => ['nullable', 'email', 'max:255'],
            'direccion'      => ['nullable', 'string', 'max:500'],
            'ciudad'         => ['nullable', 'string', 'max:100'],

            // Datos del administrador inicial (se crea junto con la empresa)
            'admin_nombre'         => ['required', 'string', 'max:150'],
            'admin_email'          => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_identificacion' => ['required', 'string', 'max:20', 'unique:users,identificacion'],
            'admin_password'       => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
