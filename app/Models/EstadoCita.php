<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoCita extends Model
{
    protected $table = 'estados_cita';

    protected $fillable = [
        'nombre',
        'color_hex',
    ];

    // Un estado puede estar asignado a muchas citas
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'estado_id');
    }
}
