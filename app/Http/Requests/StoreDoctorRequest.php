<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;
        return [
            'usuario_id'      => ['required', Rule::exists('users', 'id')->where('empresa_id', $empresaId), 'unique:medicos,usuario_id'],
            'especialidad'    => ['required', 'string', 'max:255'],
            'registro_medico' => ['required', 'string', 'max:100', 'unique:medicos,registro_medico'],
        ];
    }
}
