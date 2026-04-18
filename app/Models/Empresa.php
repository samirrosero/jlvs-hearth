<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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
        'logo_path',
        'favicon_path',
        'imagen_login_path',
        'imagen_registro_path',
        'color_primario',
        'color_secundario',
        'color_admin',
        'color_doctor',
        'color_gestor',
        'color_paciente',
        'color_pdf',
        'slogan_login',
        'slogan_registro',
    ];

    protected $appends = ['logo_url', 'favicon_url', 'imagen_login_url', 'imagen_registro_url'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    // ── URL pública del logo (para vistas web) ───────────────────────────
    public function getLogoUrlAttribute(): string
    {
        return $this->logo_path
            ? Storage::disk('public')->url($this->logo_path)
            : asset('img/logos/logo1.png');
    }

    // ── URL pública del favicon ──────────────────────────────────────────
    public function getFaviconUrlAttribute(): string
    {
        return $this->favicon_path
            ? Storage::disk('public')->url($this->favicon_path)
            : asset('favicon.ico');
    }

    // ── URL pública de la imagen del panel de login ──────────────────────
    public function getImagenLoginUrlAttribute(): ?string
    {
        return $this->imagen_login_path
            ? Storage::disk('public')->url($this->imagen_login_path)
            : null;
    }

    // ── URL pública de la imagen del panel de registro ───────────────────
    public function getImagenRegistroUrlAttribute(): ?string
    {
        return $this->imagen_registro_path
            ? Storage::disk('public')->url($this->imagen_registro_path)
            : null;
    }

    // ── Ruta absoluta del logo para DomPDF (no acepta URLs HTTP) ────────
    public function getLogoPdfPathAttribute(): string
    {
        return $this->logo_path
            ? storage_path('app/public/' . $this->logo_path)
            : public_path('img/logos/logo1.png');
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
