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

        $empresa->update($datos);

        return back()->with('exito', 'Identidad visual actualizada correctamente.');
    }
}
