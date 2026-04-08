<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EjecucionCita extends Model
{
    protected $table = 'ejecuciones_cita';

    protected $fillable = [
        'cita_id',
        'inicio_atencion',
        'fin_atencion',
        'duracion_minutos',
    ];

    protected function casts(): array
    {
        return [
            'inicio_atencion' => 'datetime',
            'fin_atencion'    => 'datetime',
        ];
    }

    // Una ejecución pertenece a una cita
    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    // Una ejecución genera una historia clínica
    public function historiaClinica(): HasOne
    {
        return $this->hasOne(HistoriaClinica::class, 'ejecucion_cita_id');
    }

    // Una ejecución tiene un registro de signos vitales
    public function signosVitales(): HasOne
    {
        return $this->hasOne(SignosVitales::class, 'ejecucion_cita_id');
    }
}
