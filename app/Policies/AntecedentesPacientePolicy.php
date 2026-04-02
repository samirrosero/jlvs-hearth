<?php

namespace App\Policies;

use App\Models\AntecedentesPaciente;
use App\Models\User;

class AntecedentesPacientePolicy
{
    public function view(User $user, AntecedentesPaciente $antecedente): bool
    {
        if (!$this->mismaTenant($user, $antecedente)) return false;

        // El paciente solo puede ver sus propios antecedentes
        if ($user->rol?->nombre === 'paciente') {
            return $user->paciente?->id === $antecedente->paciente_id;
        }

        return true;
    }

    public function update(User $user, AntecedentesPaciente $antecedente): bool
    {
        return $this->mismaTenant($user, $antecedente)
            && $user->rol?->nombre !== 'paciente';
    }

    public function delete(User $user, AntecedentesPaciente $antecedente): bool
    {
        return $this->mismaTenant($user, $antecedente)
            && $user->rol?->nombre !== 'paciente';
    }

    private function mismaTenant(User $user, AntecedentesPaciente $antecedente): bool
    {
        return $user->empresa_id === $antecedente->paciente?->empresa_id;
    }
}
