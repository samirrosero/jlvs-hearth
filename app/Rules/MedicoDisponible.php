<?php

namespace App\Rules;

use App\Models\Cita;
use App\Models\HorarioMedico;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

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
            return;
        }

        $diaSemana = (int) \Carbon\Carbon::parse($fecha)->format('w');

        // 🔹 1. Validar horario del médico
        $horario = HorarioMedico::where('medico_id', $medicoId)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->where('hora_inicio', '<=', $hora)
            ->where('hora_fin', '>', $hora)
            ->exists();

        if (!$horario) {
            $fail('El médico no está disponible en ese horario.');
            return;
        }

        // 🔹 2. Validar solapamiento de citas
        $duracionNueva = 30;

        if (!empty($this->data['servicio_id'])) {
            $servicio = \App\Models\Servicio::find($this->data['servicio_id']);
            if ($servicio) {
                $duracionNueva = $servicio->duracion_minutos;
            }
        }

        $horaIniciaNueva = \Carbon\Carbon::parse("{$fecha} {$hora}");
        $horaFinNueva    = $horaIniciaNueva->copy()->addMinutes($duracionNueva);

        $solapada = Cita::where('medico_id', $medicoId)
            ->where('fecha', $fecha)
            ->where('activo', true)
            ->whereRaw('hora < ?', [$horaFinNueva->format('H:i')])
            ->whereExists(function ($query) use ($horaIniciaNueva) {
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
            $fail('El médico no está disponible en ese horario.');
        }
    }
}