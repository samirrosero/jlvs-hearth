<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Agrega campos de documentación específica por rol a las solicitudes
// - Médicos: especialidad, número tarjeta profesional, foto diploma
// - Gestores/Admins: certificación o nombramiento
// -----------------------------------------------------------------------

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_empleador', function (Blueprint $table) {
            // Campos para médicos
            $table->string('especialidad')->nullable()->after('rol_solicitado');
            $table->string('numero_tarjeta_profesional')->nullable()->after('especialidad');
            $table->string('foto_diploma_path')->nullable()->after('foto_documento_path');
            
            // Campo genérico para documentación adicional (según el rol)
            $table->string('documento_acreditacion_path')->nullable()->after('foto_diploma_path');
            
            // Indicador de correo enviado al aprobar
            $table->boolean('correo_bienvenida_enviado')->default(false)->after('observaciones');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_empleador', function (Blueprint $table) {
            $table->dropColumn([
                'especialidad',
                'numero_tarjeta_profesional',
                'foto_diploma_path',
                'documento_acreditacion_path',
                'correo_bienvenida_enviado',
            ]);
        });
    }
};
