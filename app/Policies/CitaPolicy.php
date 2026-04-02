<?php

namespace App\Policies;

use App\Models\Cita;
use App\Models\User;

class CitaPolicy
{
    public function view(User $user, Cita $cita): bool
    {
        if ($user->empresa_id !== $cita->empresa_id) {
            return false;
        }
        if ($user->rol?->nombre === 'medico') {
            return $user->medico?->id === $cita->medico_id;
        }
        if ($user->rol?->nombre === 'paciente') {
            return $user->paciente?->id === $cita->paciente_id;
        }
        return true;
    }

    public function update(User $user, Cita $cita): bool
    {
        return $user->empresa_id === $cita->empresa_id;
    }

    public function delete(User $user, Cita $cita): bool
    {
        return $user->empresa_id === $cita->empresa_id;
    }
}
