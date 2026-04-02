<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'nit',
        'nombre',
        'telefono',
        'correo',
        'direccion',
        'ciudad',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    // Una empresa tiene muchos usuarios
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    // Una empresa tiene muchos pacientes
    public function pacientes(): HasMany
    {
        return $this->hasMany(Paciente::class, 'empresa_id');
    }

    // Una empresa tiene muchos médicos
    public function medicos(): HasMany
    {
        return $this->hasMany(Medico::class, 'empresa_id');
    }

    // Una empresa tiene muchas citas
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'empresa_id');
    }

    // Una empresa tiene muchos portafolios (convenios)
    public function portafolios(): HasMany
    {
        return $this->hasMany(Portafolio::class, 'empresa_id');
    }
}
