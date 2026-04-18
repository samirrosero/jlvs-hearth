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
        'especialidad',
        'numero_tarjeta_profesional',
        'departamento',
        'municipio',
        'foto_documento_path',
        'foto_diploma_path',
        'documento_acreditacion_path',
        'estado',
        'observaciones',
        'correo_bienvenida_enviado',
    ];

    protected $appends = ['foto_url', 'foto_diploma_url', 'documento_acreditacion_url'];

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

    public function getFotoDiplomaUrlAttribute(): ?string
    {
        return $this->foto_diploma_path
            ? Storage::disk('public')->url($this->foto_diploma_path)
            : null;
    }

    public function getDocumentoAcreditacionUrlAttribute(): ?string
    {
        return $this->documento_acreditacion_path
            ? Storage::disk('public')->url($this->documento_acreditacion_path)
            : null;
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }
}
