<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PrecioServicio;

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

    public function precios(): HasMany
    {
        return $this->hasMany(PrecioServicio::class, 'servicio_id');
    }
}
