<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecetaMedica extends Model
{
    protected $table = 'recetas_medicas';

    protected $fillable = [
        'historia_clinica_id',
        'medicamentos',
        'indicaciones',
    ];

    // Una receta pertenece a una historia clínica
    public function historiaClinica(): BelongsTo
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_clinica_id');
    }
}
