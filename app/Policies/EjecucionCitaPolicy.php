<?php

namespace App\Policies;

use App\Models\EjecucionCita;
use App\Models\User;

class EjecucionCitaPolicy
{
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
