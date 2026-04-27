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
        // Iconos del sidebar admin
        'icono_dashboard_path',
        'icono_pacientes_path',
        'icono_medicos_path',
        'icono_reportes_path',
        'icono_solicitudes_path',
        'icono_identidad_path',
        // Iconos de las tarjetas del dashboard
        'icono_card_pacientes_path',
        'icono_card_medicos_path',
        'icono_card_citas_path',
        'icono_card_total_path',
        // Iconos del portal del paciente
        'icono_pac_inicio_path',
        'icono_pac_citas_path',
        'icono_pac_historial_path',
        'icono_pac_perfil_path',
    ];

    protected $appends = [
        'logo_url', 'favicon_url', 'imagen_login_url', 'imagen_registro_url',
        // Iconos sidebar admin
        'icono_dashboard_url', 'icono_pacientes_url', 'icono_medicos_url',
        'icono_reportes_url', 'icono_solicitudes_url', 'icono_identidad_url',
        // Iconos tarjetas dashboard
        'icono_card_pacientes_url', 'icono_card_medicos_url',
        'icono_card_citas_url', 'icono_card_total_url',
        // Iconos portal paciente
        'icono_pac_inicio_url', 'icono_pac_citas_url',
        'icono_pac_historial_url', 'icono_pac_perfil_url',
    ];

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

    // ── Iconos del sidebar administrativo ───────────────────────────────
    public function getIconoDashboardUrlAttribute(): ?string
    {
        return $this->icono_dashboard_path ? Storage::disk('public')->url($this->icono_dashboard_path) : null;
    }
    public function getIconoPacientesUrlAttribute(): ?string
    {
        return $this->icono_pacientes_path ? Storage::disk('public')->url($this->icono_pacientes_path) : null;
    }
    public function getIconoMedicosUrlAttribute(): ?string
    {
        return $this->icono_medicos_path ? Storage::disk('public')->url($this->icono_medicos_path) : null;
    }
    public function getIconoReportesUrlAttribute(): ?string
    {
        return $this->icono_reportes_path ? Storage::disk('public')->url($this->icono_reportes_path) : null;
    }
    public function getIconoSolicitudesUrlAttribute(): ?string
    {
        return $this->icono_solicitudes_path ? Storage::disk('public')->url($this->icono_solicitudes_path) : null;
    }
    public function getIconoIdentidadUrlAttribute(): ?string
    {
        return $this->icono_identidad_path ? Storage::disk('public')->url($this->icono_identidad_path) : null;
    }

    // ── Iconos de las tarjetas del dashboard ──────────────────────────────
    public function getIconoCardPacientesUrlAttribute(): ?string
    {
        return $this->icono_card_pacientes_path ? Storage::disk('public')->url($this->icono_card_pacientes_path) : null;
    }
    public function getIconoCardMedicosUrlAttribute(): ?string
    {
        return $this->icono_card_medicos_path ? Storage::disk('public')->url($this->icono_card_medicos_path) : null;
    }
    public function getIconoCardCitasUrlAttribute(): ?string
    {
        return $this->icono_card_citas_path ? Storage::disk('public')->url($this->icono_card_citas_path) : null;
    }
    public function getIconoCardTotalUrlAttribute(): ?string
    {
        return $this->icono_card_total_path ? Storage::disk('public')->url($this->icono_card_total_path) : null;
    }

    // ── Iconos del portal del paciente ────────────────────────────────────
    public function getIconoPacInicioUrlAttribute(): ?string
    {
        return $this->icono_pac_inicio_path ? Storage::disk('public')->url($this->icono_pac_inicio_path) : null;
    }
    public function getIconoPacCitasUrlAttribute(): ?string
    {
        return $this->icono_pac_citas_path ? Storage::disk('public')->url($this->icono_pac_citas_path) : null;
    }
    public function getIconoPacHistorialUrlAttribute(): ?string
    {
        return $this->icono_pac_historial_path ? Storage::disk('public')->url($this->icono_pac_historial_path) : null;
    }
    public function getIconoPacPerfilUrlAttribute(): ?string
    {
        return $this->icono_pac_perfil_path ? Storage::disk('public')->url($this->icono_pac_perfil_path) : null;
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
