<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: historias_clinicas
// Descripción: Almacena el registro médico completo generado durante la
// atención de una cita. Es el documento clínico principal del sistema y
// el que reemplaza las historias escritas a mano.
//
// Cada historia está vinculada a una ejecución de cita (momento real de
// atención) y al paciente, permitiendo consultar el historial completo.
//
// El campo 'antecedentes' es JSON para manejar de forma flexible los
// diferentes tipos: familiares, quirúrgicos, alérgicos, farmacológicos.
// -----------------------------------------------------------------------

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('historias_clinicas', function (Blueprint $table) {
            $table->id();

            // --- Relaciones ---
            $table->foreignId('ejecucion_cita_id')      // Consulta en la que se generó esta historia
                ->unique()                              // Una ejecución genera exactamente una historia
                ->constrained('ejecuciones_cita')
                ->onDelete('cascade');

            $table->foreignId('paciente_id')            // Paciente al que pertenece la historia
                ->constrained('pacientes');

            // --- Contenido clínico ---
            $table->text('motivo_consulta');            // ¿Por qué consulta el paciente hoy?
            $table->text('enfermedad_actual');          // Descripción detallada de la enfermedad actual
            $table->json('antecedentes')->nullable();   // Antecedentes: familiares, quirúrgicos, alérgicos, etc.
            $table->text('diagnostico');                // Diagnóstico médico establecido
            $table->text('plan_tratamiento');           // Plan de tratamiento indicado
            $table->text('evaluacion')->nullable();     // Evaluación de evolución del paciente
            $table->text('observaciones')->nullable();  // Notas adicionales del médico

            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('historias_clinicas');
    }
};
