<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cita extends Model
{
    protected $table = 'citas';

    protected $fillable = [
        'empresa_id',
        'medico_id',
        'paciente_id',
        'estado_id',
        'modalidad_id',
        'portafolio_id',
        'servicio_id',
        'fecha',
        'hora',
        'link_videollamada',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha'  => 'date',
            'hora'   => 'string',
            'activo' => 'boolean',
        ];
    }

    // Una cita pertenece a una empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // Una cita es atendida por un médico
    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    // Una cita pertenece a un paciente
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    // Una cita tiene un estado (pendiente, atendida, cancelada...)
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoCita::class, 'estado_id');
    }

    // Una cita tiene una modalidad (presencial, telemedicina...)
    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(ModalidadCita::class, 'modalidad_id');
    }

    // Una cita usa un convenio del portafolio
    public function portafolio(): BelongsTo
    {
        return $this->belongsTo(Portafolio::class, 'portafolio_id');
    }

    // Una cita puede estar asociada a un servicio/procedimiento
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    // Una cita puede generar una ejecución (cuando se atiende)
    public function ejecucion(): HasOne
    {
        return $this->hasOne(EjecucionCita::class, 'cita_id');
    }

    // Una cita puede tener una valoración del paciente
    public function valoracion(): HasOne
    {
        return $this->hasOne(Valoracion::class, 'cita_id');
    }

    // Una cita puede tener múltiples registros de pago
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'cita_id');
    }
}
