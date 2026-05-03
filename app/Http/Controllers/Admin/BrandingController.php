<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function edit(): View
    {
        $empresa = auth()->user()->empresa;
        return view('admin.branding.edit', compact('empresa'));
    }

    public function update(Request $request): RedirectResponse
    {
        $empresa = auth()->user()->empresa;

        $request->validate([
            'slogan_login'     => ['nullable', 'string', 'max:200'],
            'slogan_registro'  => ['nullable', 'string', 'max:200'],
            'color_primario'   => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_secundario' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_admin'      => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_doctor'     => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_gestor'     => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_paciente'   => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_pdf'        => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo'             => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'favicon'          => ['nullable', 'file', 'mimes:png,ico,svg', 'max:512'],
            'imagen_login'     => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'imagen_registro'  => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            // Iconos del sidebar (PNG, idealmente 24x24 o 32x32)
            'icono_dashboard'  => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_pacientes'  => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_medicos'    => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_reportes'   => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_solicitudes'=> ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_identidad'  => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            // Iconos de las tarjetas del dashboard (PNG, idealmente 40x40 o 48x48)
            'icono_card_pacientes' => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_card_medicos'   => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_card_citas'     => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_card_total'     => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            // Iconos portal paciente
            'icono_pac_inicio'    => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_pac_citas'     => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_pac_historial' => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_pac_perfil'    => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            // Iconos panel del médico
            'icono_medico_dashboard'  => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_medico_agenda'     => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_medico_citas'      => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_medico_pacientes'  => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_medico_horario'    => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_medico_ordenes'    => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_medico_perfil'     => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            // Icono horarios + nuevos iconos admin
            'icono_horarios'          => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_servicios'         => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_convenios'         => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_auditoria'         => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_valoraciones'      => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_importar'          => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            // Iconos panel del gestor de citas
            'icono_gestor_dashboard'  => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_gestor_nueva_cita' => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_gestor_citas'      => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_gestor_espera'     => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_gestor_registrar'  => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_gestor_pacientes'  => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'icono_gestor_recepcion'  => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
        ]);

        $datos = $request->only([
            'slogan_login', 'slogan_registro',
            'color_primario', 'color_secundario',
            'color_admin', 'color_doctor', 'color_gestor',
            'color_paciente', 'color_pdf',
        ]);

        // ── Logo ─────────────────────────────────────────────────────────
        if ($request->hasFile('logo')) {
            if ($empresa->logo_path) {
                Storage::disk('public')->delete($empresa->logo_path);
            }
            $datos['logo_path'] = $request->file('logo')
                ->store("empresas/{$empresa->id}", 'public');
        }

        // ── Favicon ───────────────────────────────────────────────────────
        if ($request->hasFile('favicon')) {
            if ($empresa->favicon_path) {
                Storage::disk('public')->delete($empresa->favicon_path);
            }
            $datos['favicon_path'] = $request->file('favicon')
                ->store("empresas/{$empresa->id}", 'public');
        }

        // ── Imagen panel de login ─────────────────────────────────────────
        if ($request->hasFile('imagen_login')) {
            if ($empresa->imagen_login_path) {
                Storage::disk('public')->delete($empresa->imagen_login_path);
            }
            $datos['imagen_login_path'] = $request->file('imagen_login')
                ->store("empresas/{$empresa->id}", 'public');
        }

        // ── Imagen panel de registro ──────────────────────────────────────
        if ($request->hasFile('imagen_registro')) {
            if ($empresa->imagen_registro_path) {
                Storage::disk('public')->delete($empresa->imagen_registro_path);
            }
            $datos['imagen_registro_path'] = $request->file('imagen_registro')
                ->store("empresas/{$empresa->id}", 'public');
        }

        // ── Iconos del sidebar ─────────────────────────────────────────────
        $iconosSidebar = [
            'icono_dashboard', 'icono_pacientes', 'icono_medicos',
            'icono_reportes', 'icono_solicitudes', 'icono_identidad',
            'icono_horarios', 'icono_servicios', 'icono_convenios', 'icono_auditoria',
            'icono_valoraciones', 'icono_importar',
        ];
        foreach ($iconosSidebar as $icono) {
            if ($request->hasFile($icono)) {
                $pathField = $icono . '_path';
                if ($empresa->$pathField) {
                    Storage::disk('public')->delete($empresa->$pathField);
                }
                $datos[$pathField] = $request->file($icono)
                    ->store("empresas/{$empresa->id}/iconos", 'public');
            }
        }

        // ── Iconos de las tarjetas del dashboard ────────────────────────────
        $iconosCards = [
            'icono_card_pacientes', 'icono_card_medicos',
            'icono_card_citas', 'icono_card_total'
        ];
        foreach ($iconosCards as $icono) {
            if ($request->hasFile($icono)) {
                $pathField = $icono . '_path';
                if ($empresa->$pathField) {
                    Storage::disk('public')->delete($empresa->$pathField);
                }
                $datos[$pathField] = $request->file($icono)
                    ->store("empresas/{$empresa->id}/iconos", 'public');
            }
        }

        // ── Iconos del portal del paciente ────────────────────────────────────
        $iconosPaciente = [
            'icono_pac_inicio', 'icono_pac_citas',
            'icono_pac_historial', 'icono_pac_perfil',
        ];
        foreach ($iconosPaciente as $icono) {
            if ($request->hasFile($icono)) {
                $pathField = $icono . '_path';
                if ($empresa->$pathField) {
                    Storage::disk('public')->delete($empresa->$pathField);
                }
                $datos[$pathField] = $request->file($icono)
                    ->store("empresas/{$empresa->id}/iconos", 'public');
            }
        }

        // ── Iconos del panel del médico ──────────────────────────────────────
        $iconosMedico = [
            'icono_medico_dashboard', 'icono_medico_agenda',
            'icono_medico_citas', 'icono_medico_pacientes',
            'icono_medico_horario', 'icono_medico_ordenes', 'icono_medico_perfil',
        ];
        foreach ($iconosMedico as $icono) {
            if ($request->hasFile($icono)) {
                $pathField = $icono . '_path';
                if ($empresa->$pathField) {
                    Storage::disk('public')->delete($empresa->$pathField);
                }
                $datos[$pathField] = $request->file($icono)
                    ->store("empresas/{$empresa->id}/iconos", 'public');
            }
        }

        // ── Iconos del panel del gestor de citas ─────────────────────────────
        $iconosGestor = [
            'icono_gestor_dashboard', 'icono_gestor_nueva_cita',
            'icono_gestor_citas', 'icono_gestor_espera',
            'icono_gestor_registrar', 'icono_gestor_pacientes',
            'icono_gestor_recepcion',
        ];
        foreach ($iconosGestor as $icono) {
            if ($request->hasFile($icono)) {
                $pathField = $icono . '_path';
                if ($empresa->$pathField) {
                    Storage::disk('public')->delete($empresa->$pathField);
                }
                $datos[$pathField] = $request->file($icono)
                    ->store("empresas/{$empresa->id}/iconos", 'public');
            }
        }

        $empresa->update($datos);

        return back()->with('exito', 'Identidad visual actualizada correctamente.');
    }
}
