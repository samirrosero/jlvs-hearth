<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: citas
// Descripción: Representa el agendamiento de una consulta médica.
// Es la entidad central del sistema: conecta al paciente con el médico,
// define la fecha/hora, la modalidad de atención y el convenio usado.
//
// Estados del ciclo de vida → ver tabla 'estados_cita'
// -----------------------------------------------------------------------

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();

            // --- Relaciones principales ---
            $table->foreignId('empresa_id')             // IPS donde se atiende la cita
                ->constrained('empresas')
                ->onDelete('cascade');

            $table->foreignId('medico_id')              // Médico asignado a la cita
                ->constrained('medicos');

            $table->foreignId('paciente_id')            // Paciente que agenda la cita
                ->constrained('pacientes');

            $table->foreignId('estado_id')              // Estado actual de la cita
                ->constrained('estados_cita');

            $table->foreignId('modalidad_id')           // Modalidad de atención (presencial, telemedicina...)
                ->constrained('modalidades_cita');

            $table->foreignId('portafolio_id')          // Convenio con el que se atiende (EPS, particular...) — opcional para citas particulares
                ->nullable()
                ->constrained('portafolios')
                ->onDelete('set null');

            // --- Datos de la cita ---
            $table->date('fecha');                      // Fecha programada de la cita
            $table->time('hora');                       // Hora programada de la cita
            $table->boolean('activo')->default(true);  // Permite archivar citas sin borrarlas

            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
