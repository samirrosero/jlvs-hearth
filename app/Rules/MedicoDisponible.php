<?php

namespace App\Rules;

use App\Models\Cita;
use App\Models\HorarioMedico;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Verifica que el médico tenga horario definido para el día y hora de la cita,
 * y que no tenga otra cita activa en ese mismo bloque de tiempo.
 *
 * Uso en rules():
 *   'medico_id' => ['required', new MedicoDisponible($request->fecha, $request->hora)],
 */
class MedicoDisponible implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $medicoId = $value;
        $fecha    = $this->data['fecha'] ?? null;
        $hora     = $this->data['hora']  ?? null;

        if (!$fecha || !$hora) {
            return; // Las otras reglas ya validan fecha/hora
        }

        $diaSemana = (int) \Carbon\Carbon::parse($fecha)->format('w'); // 0=dom…6=sáb

        // 1. Verificar que el médico tiene horario ese día
        $horario = HorarioMedico::where('medico_id', $medicoId)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->where('hora_inicio', '<=', $hora)
            ->where('hora_fin', '>', $hora)
            ->exists();

        if (!$horario) {
            $fail('El médico no tiene disponibilidad el día y hora indicados.');
            return;
        }

        // 2. Verificar que no tenga otra cita activa en ese mismo momento
        // (considera la duración del servicio si viene en la request)
        $solapada = Cita::where('medico_id', $medicoId)
            ->where('fecha', $fecha)
            ->where('hora', $hora)
            ->where('activo', true)
            ->exists();

        if ($solapada) {
            $fail('El médico ya tiene una cita programada en ese horario.');
        }
    }
}
