<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoriaClinica extends Model
{
    protected $table = 'historias_clinicas';

    protected $fillable = [
        'ejecucion_cita_id',
        'paciente_id',
        'motivo_consulta',
        'enfermedad_actual',
        'antecedentes',
        'diagnostico',
        'codigo_cie10',
        'descripcion_cie10',
        'plan_tratamiento',
        'evaluacion',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            // El campo antecedentes se guarda como JSON y se recupera como array
            'antecedentes' => 'array',
        ];
    }

    // Una historia clínica pertenece a una ejecución de cita
    public function ejecucionCita(): BelongsTo
    {
        return $this->belongsTo(EjecucionCita::class, 'ejecucion_cita_id');
    }

    // Una historia clínica pertenece a un paciente
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    // Una historia puede tener una o varias recetas médicas
    public function recetasMedicas(): HasMany
    {
        return $this->hasMany(RecetaMedica::class, 'historia_clinica_id');
    }

    // Una historia puede tener varios documentos adjuntos
    public function documentosAdjuntos(): HasMany
    {
        return $this->hasMany(DocumentoAdjunto::class, 'historia_clinica_id');
    }

    // Una historia puede tener varias órdenes médicas
    public function ordenesMedicas(): HasMany
    {
        return $this->hasMany(OrdenMedica::class, 'historia_clinica_id');
    }
}
