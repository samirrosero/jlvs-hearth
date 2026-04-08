<?php

namespace App\Policies;

use App\Models\EjecucionCita;
use App\Models\User;

class EjecucionCitaPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->rol?->nombre, ['administrador', 'medico']);
    }

    private function mismaTenant(User $user, EjecucionCita $ejecucion): bool
    {
        return $user->empresa_id === $ejecucion->cita?->empresa_id;
    }

    public function view(User $user, EjecucionCita $ejecucion): bool
    {
        return $this->mismaTenant($user, $ejecucion);
    }

    public function update(User $user, EjecucionCita $ejecucion): bool
    {
        return $this->mismaTenant($user, $ejecucion);
    }

    public function delete(User $user, EjecucionCita $ejecucion): bool
    {
        return $this->mismaTenant($user, $ejecucion);
    }
}
