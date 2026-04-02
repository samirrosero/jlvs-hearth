<?php

namespace App\Policies;

use App\Models\Medico;
use App\Models\User;

class MedicoPolicy
{
    public function view(User $user, Medico $medico): bool
    {
        return $user->empresa_id === $medico->empresa_id;
    }

    public function update(User $user, Medico $medico): bool
    {
        return $user->empresa_id === $medico->empresa_id;
    }

    public function delete(User $user, Medico $medico): bool
    {
        return $user->empresa_id === $medico->empresa_id;
    }
}
