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
            'logo'             => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'favicon'          => ['nullable', 'image', 'mimes:png,ico,svg', 'max:512'],
            'imagen_login'     => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'imagen_registro'  => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            // Iconos del sidebar (PNG, idealmente 24x24 o 32x32)
            'icono_dashboard'  => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            'icono_pacientes'  => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            'icono_medicos'    => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            'icono_reportes'   => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            'icono_solicitudes'=> ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            'icono_identidad'  => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            // Iconos de las tarjetas del dashboard (PNG, idealmente 40x40 o 48x48)
            'icono_card_pacientes' => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            'icono_card_medicos'   => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            'icono_card_citas'     => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            'icono_card_total'     => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            // Iconos portal paciente
            'icono_pac_inicio'    => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            'icono_pac_citas'     => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            'icono_pac_historial' => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            'icono_pac_perfil'    => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            // Iconos panel del médico
            'icono_medico_dashboard'  => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            'icono_medico_citas'      => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            'icono_medico_pacientes'  => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
            // Icono horarios (admin)
            'icono_horarios'          => ['nullable', 'image', 'mimes:png,svg,webp', 'max:512'],
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
            'icono_reportes', 'icono_solicitudes', 'icono_identidad'
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
            'icono_medico_dashboard', 'icono_medico_citas', 'icono_medico_pacientes',
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

        // ── Icono de Horarios (admin) ─────────────────────────────────────────
        if ($request->hasFile('icono_horarios')) {
            if ($empresa->icono_horarios_path) {
                Storage::disk('public')->delete($empresa->icono_horarios_path);
            }
            $datos['icono_horarios_path'] = $request->file('icono_horarios')
                ->store("empresas/{$empresa->id}/iconos", 'public');
        }

        $empresa->update($datos);

        return back()->with('exito', 'Identidad visual actualizada correctamente.');
    }
}
