<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    protected $table = 'servicios';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'descripcion',
        'duracion_minutos',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo'            => 'boolean',
            'duracion_minutos'  => 'integer',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'servicio_id');
    }
}
