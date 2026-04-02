<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: ejecuciones_cita
// Descripción: Registra el momento real en que una cita es atendida.
// Separamos el agendamiento (citas) de la ejecución real para poder
// medir tiempos de atención, calcular duración y controlar el flujo
// de atención del médico.
//
// Una cita puede tener como máximo UNA ejecución.
// -----------------------------------------------------------------------

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('ejecuciones_cita', function (Blueprint $table) {
            $table->id();

            // Cada ejecución corresponde exactamente a una cita
            $table->foreignId('cita_id')
                ->unique()                              // Una cita solo puede ejecutarse una vez
                ->constrained('citas')
                ->onDelete('cascade');

            $table->dateTime('inicio_atencion');        // Fecha y hora en que el médico inició la consulta
            $table->dateTime('fin_atencion')->nullable(); // Fecha y hora en que terminó la consulta
            $table->integer('duracion_minutos')->nullable(); // Duración calculada de la consulta

            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('ejecuciones_cita');
    }
};
