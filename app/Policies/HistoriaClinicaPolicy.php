<?php

namespace App\Policies;

use App\Models\HistoriaClinica;
use App\Models\User;

class HistoriaClinicaPolicy
{
    private function mismaTenant(User $user, HistoriaClinica $historia): bool
    {
        return $user->empresa_id === $historia->paciente?->empresa_id;
    }

    public function view(User $user, HistoriaClinica $historia): bool
    {
        if ($user->rol?->nombre === 'paciente') {
            return $user->paciente?->id === $historia->paciente_id;
        }
        return $this->mismaTenant($user, $historia);
    }

    public function update(User $user, HistoriaClinica $historia): bool
    {
        return $this->mismaTenant($user, $historia);
    }

    public function delete(User $user, HistoriaClinica $historia): bool
    {
        return $this->mismaTenant($user, $historia);
    }
}
