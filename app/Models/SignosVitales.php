<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignosVitales extends Model
{
    protected $table = 'signos_vitales';

    protected $fillable = [
        'ejecucion_cita_id',
        'paciente_id',
        'peso_kg',
        'talla_cm',
        'presion_sistolica',
        'presion_diastolica',
        'temperatura_c',
        'frecuencia_cardiaca',
        'saturacion_oxigeno',
        'frecuencia_respiratoria',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'peso_kg'                 => 'float',
            'talla_cm'                => 'float',
            'temperatura_c'           => 'float',
            'presion_sistolica'       => 'integer',
            'presion_diastolica'      => 'integer',
            'frecuencia_cardiaca'     => 'integer',
            'saturacion_oxigeno'      => 'integer',
            'frecuencia_respiratoria' => 'integer',
        ];
    }

    public function ejecucionCita(): BelongsTo
    {
        return $this->belongsTo(EjecucionCita::class, 'ejecucion_cita_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
}
