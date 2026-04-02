<?php

namespace App\Policies;

use App\Models\DocumentoAdjunto;
use App\Models\User;

class DocumentoAdjuntoPolicy
{
    private function mismaTenant(User $user, DocumentoAdjunto $documento): bool
    {
        return $user->empresa_id === $documento->historiaClinica?->paciente?->empresa_id;
    }

    public function view(User $user, DocumentoAdjunto $documento): bool
    {
        return $this->mismaTenant($user, $documento);
    }

    public function update(User $user, DocumentoAdjunto $documento): bool
    {
        return $this->mismaTenant($user, $documento);
    }

    public function delete(User $user, DocumentoAdjunto $documento): bool
    {
        return $this->mismaTenant($user, $documento);
    }
}
