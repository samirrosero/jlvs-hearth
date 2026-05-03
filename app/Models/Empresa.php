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
        // Iconos del panel del médico
        'icono_medico_dashboard_path',
        'icono_medico_citas_path',
        'icono_medico_pacientes_path',
        // Icono de Horarios (admin)
        'icono_horarios_path',
        // Iconos nuevos del sidebar admin
        'icono_servicios_path',
        'icono_convenios_path',
        'icono_auditoria_path',
        'icono_valoraciones_path',
        'icono_importar_path',
        // Iconos del panel del gestor de citas
        'icono_gestor_dashboard_path',
        'icono_gestor_nueva_cita_path',
        'icono_gestor_citas_path',
        'icono_gestor_espera_path',
        'icono_gestor_registrar_path',
        'icono_gestor_pacientes_path',
        'icono_gestor_recepcion_path',
        // Iconos adicionales del panel del médico
        'icono_medico_agenda_path',
        'icono_medico_horario_path',
        'icono_medico_ordenes_path',
        'icono_medico_perfil_path',
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
        // Iconos panel médico
        'icono_medico_dashboard_url', 'icono_medico_agenda_url',
        'icono_medico_citas_url', 'icono_medico_pacientes_url',
        'icono_medico_horario_url', 'icono_medico_ordenes_url', 'icono_medico_perfil_url',
        // Icono horarios (admin)
        'icono_horarios_url',
        // Iconos nuevos del sidebar admin
        'icono_servicios_url', 'icono_convenios_url', 'icono_auditoria_url',
        'icono_valoraciones_url', 'icono_importar_url',
        // Iconos panel gestor de citas
        'icono_gestor_dashboard_url', 'icono_gestor_nueva_cita_url',
        'icono_gestor_citas_url', 'icono_gestor_espera_url',
        'icono_gestor_registrar_url', 'icono_gestor_pacientes_url',
        'icono_gestor_recepcion_url',
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

    // ── Iconos del panel del médico ───────────────────────────────────────
    public function getIconoMedicoDashboardUrlAttribute(): ?string
    {
        return $this->icono_medico_dashboard_path ? Storage::disk('public')->url($this->icono_medico_dashboard_path) : null;
    }
    public function getIconoMedicoAgendaUrlAttribute(): ?string
    {
        return $this->icono_medico_agenda_path ? Storage::disk('public')->url($this->icono_medico_agenda_path) : null;
    }
    public function getIconoMedicoCitasUrlAttribute(): ?string
    {
        return $this->icono_medico_citas_path ? Storage::disk('public')->url($this->icono_medico_citas_path) : null;
    }
    public function getIconoMedicoPacientesUrlAttribute(): ?string
    {
        return $this->icono_medico_pacientes_path ? Storage::disk('public')->url($this->icono_medico_pacientes_path) : null;
    }
    public function getIconoMedicoHorarioUrlAttribute(): ?string
    {
        return $this->icono_medico_horario_path ? Storage::disk('public')->url($this->icono_medico_horario_path) : null;
    }
    public function getIconoMedicoOrdenesUrlAttribute(): ?string
    {
        return $this->icono_medico_ordenes_path ? Storage::disk('public')->url($this->icono_medico_ordenes_path) : null;
    }
    public function getIconoMedicoPerfilUrlAttribute(): ?string
    {
        return $this->icono_medico_perfil_path ? Storage::disk('public')->url($this->icono_medico_perfil_path) : null;
    }

    // ── Icono de Horarios (admin) ─────────────────────────────────────────
    public function getIconoHorariosUrlAttribute(): ?string
    {
        return $this->icono_horarios_path ? Storage::disk('public')->url($this->icono_horarios_path) : null;
    }

    // ── Iconos nuevos del sidebar admin ──────────────────────────────────
    public function getIconoServiciosUrlAttribute(): ?string
    {
        return $this->icono_servicios_path ? Storage::disk('public')->url($this->icono_servicios_path) : null;
    }
    public function getIconoConveniosUrlAttribute(): ?string
    {
        return $this->icono_convenios_path ? Storage::disk('public')->url($this->icono_convenios_path) : null;
    }
    public function getIconoAuditoriaUrlAttribute(): ?string
    {
        return $this->icono_auditoria_path ? Storage::disk('public')->url($this->icono_auditoria_path) : null;
    }
    public function getIconoValoracionesUrlAttribute(): ?string
    {
        return $this->icono_valoraciones_path ? Storage::disk('public')->url($this->icono_valoraciones_path) : null;
    }
    public function getIconoImportarUrlAttribute(): ?string
    {
        return $this->icono_importar_path ? Storage::disk('public')->url($this->icono_importar_path) : null;
    }

    // ── Iconos del panel del gestor de citas ─────────────────────────────
    public function getIconoGestorDashboardUrlAttribute(): ?string
    {
        return $this->icono_gestor_dashboard_path ? Storage::disk('public')->url($this->icono_gestor_dashboard_path) : null;
    }
    public function getIconoGestorNuevaCitaUrlAttribute(): ?string
    {
        return $this->icono_gestor_nueva_cita_path ? Storage::disk('public')->url($this->icono_gestor_nueva_cita_path) : null;
    }
    public function getIconoGestorCitasUrlAttribute(): ?string
    {
        return $this->icono_gestor_citas_path ? Storage::disk('public')->url($this->icono_gestor_citas_path) : null;
    }
    public function getIconoGestorEsperaUrlAttribute(): ?string
    {
        return $this->icono_gestor_espera_path ? Storage::disk('public')->url($this->icono_gestor_espera_path) : null;
    }
    public function getIconoGestorRegistrarUrlAttribute(): ?string
    {
        return $this->icono_gestor_registrar_path ? Storage::disk('public')->url($this->icono_gestor_registrar_path) : null;
    }
    public function getIconoGestorPacientesUrlAttribute(): ?string
    {
        return $this->icono_gestor_pacientes_path ? Storage::disk('public')->url($this->icono_gestor_pacientes_path) : null;
    }
    public function getIconoGestorRecepcionUrlAttribute(): ?string
    {
        return $this->icono_gestor_recepcion_path ? Storage::disk('public')->url($this->icono_gestor_recepcion_path) : null;
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
