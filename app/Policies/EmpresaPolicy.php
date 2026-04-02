<?php

namespace App\Policies;

use App\Models\Empresa;
use App\Models\User;

class EmpresaPolicy
{
    public function view(User $user, Empresa $empresa): bool
    {
        return $user->empresa_id === $empresa->id;
    }

    public function update(User $user, Empresa $empresa): bool
    {
        return $user->empresa_id === $empresa->id;
    }

    public function delete(User $user, Empresa $empresa): bool
    {
        return $user->empresa_id === $empresa->id;
    }
}
