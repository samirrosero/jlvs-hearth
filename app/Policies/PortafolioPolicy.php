<?php

namespace App\Policies;

use App\Models\Portafolio;
use App\Models\User;

class PortafolioPolicy
{
    public function view(User $user, Portafolio $portafolio): bool
    {
        return $user->empresa_id === $portafolio->empresa_id;
    }

    public function update(User $user, Portafolio $portafolio): bool
    {
        return $user->empresa_id === $portafolio->empresa_id;
    }

    public function delete(User $user, Portafolio $portafolio): bool
    {
        return $user->empresa_id === $portafolio->empresa_id;
    }
}
