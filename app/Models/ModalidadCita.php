<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModalidadCita extends Model
{
    protected $table = 'modalidades_cita';

    protected $fillable = [
        'nombre',
    ];

    // Una modalidad puede estar en muchas citas
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'modalidad_id');
    }
}
