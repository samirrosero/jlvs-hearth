<?php

namespace App\Console\Commands;

use App\Models\Cita;
use App\Models\EstadoCita;
use Illuminate\Console\Command;

class MarcarNoAsistio extends Command
{
    protected $signature   = 'citas:marcar-no-asistio';
    protected $description = 'Marca como "No asistió" las citas de hoy que llevan más de 5 min en Pendiente';

    public function handle(): int
    {
        $noAsistio = EstadoCita::where('nombre', 'No asistió')->first();
        $pendiente  = EstadoCita::where('nombre', 'Pendiente')->first();

        if (! $noAsistio || ! $pendiente) {
            $this->error('Estados "Pendiente" o "No asistió" no encontrados en la BD.');
            return self::FAILURE;
        }

        $corte = now()->subMinutes(5)->format('H:i:s');

        $actualizadas = Cita::whereDate('fecha', today())
            ->where('activo', true)
            ->where('estado_id', $pendiente->id)
            ->whereTime('hora', '<=', $corte)
            ->update(['estado_id' => $noAsistio->id]);

        $this->info("Citas marcadas como 'No asistió': {$actualizadas}");

        return self::SUCCESS;
    }
}
