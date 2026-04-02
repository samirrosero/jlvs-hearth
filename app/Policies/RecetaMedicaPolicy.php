<?php

namespace App\Policies;

use App\Models\RecetaMedica;
use App\Models\User;

class RecetaMedicaPolicy
{
    private function mismaTenant(User $user, RecetaMedica $receta): bool
    {
        return $user->empresa_id === $receta->historiaClinica?->paciente?->empresa_id;
    }

    public function view(User $user, RecetaMedica $receta): bool
    {
        if ($user->rol?->nombre === 'paciente') {
            return $user->paciente?->id === $receta->historiaClinica?->paciente_id;
        }
        return $this->mismaTenant($user, $receta);
    }

    public function update(User $user, RecetaMedica $receta): bool
    {
        return $this->mismaTenant($user, $receta);
    }

    public function delete(User $user, RecetaMedica $receta): bool
    {
        return $this->mismaTenant($user, $receta);
    }
}
