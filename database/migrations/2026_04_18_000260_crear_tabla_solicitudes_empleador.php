<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: solicitudes_empleador
// Almacena los registros de personal (médico, gestor, admin) que se
// auto-registran en el portal. Quedan en estado 'pendiente' hasta que
// el administrador los aprueba o rechaza desde el panel.
// -----------------------------------------------------------------------

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_empleador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            // Datos personales
            $table->string('tipo_documento');          // CC, TI, CE, PP...
            $table->string('numero_documento');
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('correo')->unique();
            $table->string('password');                 // bcrypt al guardar

            // Rol solicitado
            $table->string('rol_solicitado');           // administrador | medico | gestor_citas

            // Datos adicionales de empleador
            $table->string('departamento')->nullable();
            $table->string('municipio')->nullable();

            // Foto del documento de identidad para verificación
            $table->string('foto_documento_path')->nullable();

            // Estado de la solicitud
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->text('observaciones')->nullable();  // motivo de rechazo si aplica

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_empleador');
    }
};
