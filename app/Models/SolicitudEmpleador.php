<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SolicitudEmpleador extends Model
{
    protected $table = 'solicitudes_empleador';

    protected $fillable = [
        'empresa_id',
        'tipo_documento',
        'numero_documento',
        'nombres',
        'apellidos',
        'correo',
        'password',
        'rol_solicitado',
        'departamento',
        'municipio',
        'foto_documento_path',
        'estado',
        'observaciones',
    ];

    protected $hidden = ['password'];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto_documento_path
            ? Storage::disk('public')->url($this->foto_documento_path)
            : null;
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }
}
