<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Migración: branding por IPS
// Agrega a la tabla `empresas` todas las columnas necesarias para que
// cada IPS pueda personalizar su identidad visual dentro del sistema:
// logo, favicon, y paleta de colores por vista/rol.
// -----------------------------------------------------------------------

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // ── Imágenes ──────────────────────────────────────────────
            $table->string('logo_path')->nullable()->after('activo');
            $table->string('favicon_path')->nullable()->after('logo_path');

            // ── Colores ───────────────────────────────────────────────
            // color_primario:   botones, acentos, elementos activos (todas las vistas)
            // color_secundario: textos de título, acentos secundarios
            // color_admin:      fondo del sidebar en el panel de administración
            // color_doctor:     fondo del sidebar en el panel de médico
            // color_gestor:     fondo del sidebar en el panel de gestor de citas
            // color_paciente:   fondo del sidebar en el portal del paciente
            // color_pdf:        color principal en encabezados y tablas de PDFs/reportes
            $table->string('color_primario')->default('#1e40af')->after('favicon_path');
            $table->string('color_secundario')->default('#1e3a8a')->after('color_primario');
            $table->string('color_admin')->default('#1e293b')->after('color_secundario');
            $table->string('color_doctor')->default('#064e3b')->after('color_admin');
            $table->string('color_gestor')->default('#4c1d95')->after('color_doctor');
            $table->string('color_paciente')->default('#0c4a6e')->after('color_gestor');
            $table->string('color_pdf')->default('#1e40af')->after('color_paciente');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path',
                'favicon_path',
                'color_primario',
                'color_secundario',
                'color_admin',
                'color_doctor',
                'color_gestor',
                'color_paciente',
                'color_pdf',
            ]);
        });
    }
};
