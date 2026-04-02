<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Portafolio extends Model
{
    protected $table = 'portafolios';

    protected $fillable = [
        'empresa_id',
        'nombre_convenio',
        'descripcion',
    ];

    // Un portafolio pertenece a una empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // Un portafolio (convenio) puede tener muchas citas
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'portafolio_id');
    }
}
