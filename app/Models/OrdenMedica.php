<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenMedica extends Model
{
    protected $table = 'ordenes_medicas';

    protected $fillable = [
        'historia_clinica_id',
        'paciente_id',
        'tipo',
        'descripcion',
        'instrucciones',
        'estado',
        'autorizado_en',
        'autorizado_via',
    ];

    protected function casts(): array
    {
        return [
            'autorizado_en' => 'datetime',
        ];
    }

    public function historiaClinica(): BelongsTo
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_clinica_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
}
