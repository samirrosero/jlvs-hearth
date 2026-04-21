<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Empresa;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Rol;

// -----------------------------------------------------------------------
// Modelo: User (tabla: users)
// El nombre del archivo y la clase se mantienen en inglés porque Laravel
// los requiere así para el sistema de autenticación.
// Todos los campos y relaciones están documentados en español.
// -----------------------------------------------------------------------

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'empresa_id',
        'rol_id',
        'nombre',
        'email',
        'identificacion',
        'password',
        'activo',
        'debe_cambiar_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'password'               => 'hashed',
            'activo'                 => 'boolean',
            'debe_cambiar_password'  => 'boolean',
        ];
    }

    // Un usuario pertenece a una empresa (IPS)
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // Un usuario tiene un rol que define sus permisos
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    // Si el usuario es médico, tiene un perfil de médico
    public function medico(): HasOne
    {
        return $this->hasOne(Medico::class, 'usuario_id');
    }

    // Si el usuario es paciente, tiene un perfil de paciente
    public function paciente(): HasOne
    {
        return $this->hasOne(Paciente::class, 'usuario_id');
    }

    // Método de ayuda: verifica si el usuario tiene un rol específico
    public function tieneRol(string $nombreRol): bool
    {
        return $this->rol?->nombre === $nombreRol;
    }
}
