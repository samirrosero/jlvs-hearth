<?php

namespace App\Policies;

use App\Models\SignosVitales;
use App\Models\User;

class SignosVitalesPolicy
{
    public function view(User $user, SignosVitales $signos): bool
    {
        if (!$this->mismaTenant($user, $signos)) return false;

        // El paciente solo puede ver sus propios signos
        if ($user->rol?->nombre === 'paciente') {
            return $user->paciente?->id === $signos->paciente_id;
        }

        return true;
    }

    public function update(User $user, SignosVitales $signos): bool
    {
        return $this->mismaTenant($user, $signos)
            && $user->rol?->nombre !== 'paciente';
    }

    public function delete(User $user, SignosVitales $signos): bool
    {
        return $this->mismaTenant($user, $signos)
            && $user->rol?->nombre !== 'paciente';
    }

    private function mismaTenant(User $user, SignosVitales $signos): bool
    {
        return $user->empresa_id === $signos->paciente?->empresa_id;
    }
}
