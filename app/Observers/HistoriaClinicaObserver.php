<?php

namespace App\Observers;

use App\Models\HistoriaClinica;
use App\Models\LogAuditoria;
use Illuminate\Support\Facades\Request;

class HistoriaClinicaObserver
{
    public function retrieved(HistoriaClinica $historia): void
    {
        $this->registrar('ver', $historia);
    }

    public function created(HistoriaClinica $historia): void
    {
        $this->registrar('crear', $historia);
    }

    public function updated(HistoriaClinica $historia): void
    {
        // Solo guarda los campos que realmente cambiaron
        $this->registrar('actualizar', $historia, $historia->getChanges());
    }

    public function deleted(HistoriaClinica $historia): void
    {
        $this->registrar('eliminar', $historia);
    }

    private function registrar(string $accion, HistoriaClinica $historia, array $detalles = []): void
    {
        $usuario = auth()->user();

        LogAuditoria::create([
            'usuario_id' => $usuario?->id,
            'empresa_id' => $usuario?->empresa_id,
            'accion'     => $accion,
            'modelo'     => 'HistoriaClinica',
            'modelo_id'  => $historia->id,
            'ip'         => Request::ip(),
            'detalles'   => empty($detalles) ? null : $detalles,
        ]);
    }
}
