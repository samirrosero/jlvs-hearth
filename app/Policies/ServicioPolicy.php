<?php

namespace App\Policies;

use App\Models\Servicio;
use App\Models\User;

class ServicioPolicy
{
    public function view(User $user, Servicio $servicio): bool
    {
        return $user->empresa_id === $servicio->empresa_id;
    }

    public function update(User $user, Servicio $servicio): bool
    {
        return $user->empresa_id === $servicio->empresa_id;
    }

    public function delete(User $user, Servicio $servicio): bool
    {
        return $user->empresa_id === $servicio->empresa_id;
    }
}
