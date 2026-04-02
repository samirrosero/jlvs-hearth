<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: medicos
// Descripción: Extiende la tabla de usuarios con información profesional
// específica del médico. Un médico es siempre un usuario del sistema
// con rol 'medico'. Tiene registro médico único a nivel nacional.
// -----------------------------------------------------------------------

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('medicos', function (Blueprint $table) {
            $table->id();

            // Un usuario solo puede ser médico una vez (relación 1 a 1)
            $table->foreignId('usuario_id')
                ->unique()
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('empresa_id')             // IPS a la que pertenece el médico
                ->constrained('empresas')
                ->onDelete('cascade');

            $table->string('especialidad');             // Especialidad médica (ej: 'Medicina General')
            $table->string('registro_medico')->unique(); // Número de registro profesional (único nacional)
            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicos');
    }
};
