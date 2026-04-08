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

        // 2. Verificar que no tenga otra cita activa que se solape en ese bloque de tiempo
        // Se carga la duración del servicio solicitado (default 30 min) para calcular el fin del bloque
        $duracionNueva = 30;
        if (!empty($this->data['servicio_id'])) {
            $servicio = \App\Models\Servicio::find($this->data['servicio_id']);
            if ($servicio) {
                $duracionNueva = $servicio->duracion_minutos;
            }
        }

        $horaIniciaNueva = \Carbon\Carbon::parse("{$fecha} {$hora}");
        $horaFinNueva    = $horaIniciaNueva->copy()->addMinutes($duracionNueva);

        // Una cita existente solapa si su inicio < fin_nueva Y su fin > inicio_nueva
        $solapada = Cita::where('medico_id', $medicoId)
            ->where('fecha', $fecha)
            ->where('activo', true)
            ->whereRaw('hora < ?', [$horaFinNueva->format('H:i')])
            ->whereExists(function ($query) use ($medicoId, $fecha, $horaIniciaNueva) {
                // Calcula hora_fin de cada cita existente sumando la duración de su servicio (default 30)
                $query->selectRaw('1')
                    ->from('citas as c2')
                    ->leftJoin('servicios as s', 'c2.servicio_id', '=', 's.id')
                    ->whereRaw('c2.id = citas.id')
                    ->whereRaw(
                        'ADDTIME(c2.hora, SEC_TO_TIME(COALESCE(s.duracion_minutos, 30) * 60)) > ?',
                        [$horaIniciaNueva->format('H:i:s')]
                    );
            })
            ->exists();

        if ($solapada) {
            $fail('El médico ya tiene una cita programada en ese horario.');
        }
    }
}
