<?php

namespace App\Policies;

use App\Models\HorarioMedico;
use App\Models\User;

class HorarioMedicoPolicy
{
    public function view(User $user, HorarioMedico $horario): bool
    {
        return $user->empresa_id === $horario->empresa_id;
    }

    public function update(User $user, HorarioMedico $horario): bool
    {
        return $user->empresa_id === $horario->empresa_id;
    }

    public function delete(User $user, HorarioMedico $horario): bool
    {
        return $user->empresa_id === $horario->empresa_id;
    }
}
