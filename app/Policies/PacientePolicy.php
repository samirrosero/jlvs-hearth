<?php

namespace App\Policies;

use App\Models\Paciente;
use App\Models\User;

class PacientePolicy
{
    public function view(User $user, Paciente $paciente): bool
    {
        if ($user->rol?->nombre === 'paciente') {
            return $user->paciente?->id === $paciente->id;
        }
        return $user->empresa_id === $paciente->empresa_id;
    }

    public function update(User $user, Paciente $paciente): bool
    {
        return $user->empresa_id === $paciente->empresa_id;
    }

    public function delete(User $user, Paciente $paciente): bool
    {
        return $user->empresa_id === $paciente->empresa_id;
    }
}
