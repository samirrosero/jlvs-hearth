<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medico extends Model
{
    protected $table = 'medicos';

    protected $fillable = [
        'usuario_id',
        'empresa_id',
        'especialidad',
        'registro_medico',
    ];

    // Un médico tiene un usuario del sistema (relación 1 a 1)
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Un médico pertenece a una empresa (IPS)
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // Un médico puede tener muchas citas asignadas
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'medico_id');
    }
}
