<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: signos_vitales
// Descripción: Registra las mediciones clínicas tomadas al inicio de cada
// consulta. Se vincula a la ejecución de cita (momento real de atención)
// y al paciente para construir su historial de signos a lo largo del tiempo.
//
// Todos los campos de medición son nullable: no siempre se toman todos.
// -----------------------------------------------------------------------

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signos_vitales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ejecucion_cita_id')
                ->unique()                               // Una ejecución → un registro de signos
                ->constrained('ejecuciones_cita')
                ->onDelete('cascade');

            $table->foreignId('paciente_id')
                ->constrained('pacientes');

            // Mediciones (todas nullable: depende del tipo de consulta)
            $table->decimal('peso_kg', 5, 2)->nullable();
            $table->decimal('talla_cm', 5, 2)->nullable();
            $table->unsignedSmallInteger('presion_sistolica')->nullable();    // mmHg
            $table->unsignedSmallInteger('presion_diastolica')->nullable();   // mmHg
            $table->decimal('temperatura_c', 4, 1)->nullable();               // °C
            $table->unsignedSmallInteger('frecuencia_cardiaca')->nullable();  // lpm
            $table->unsignedTinyInteger('saturacion_oxigeno')->nullable();    // %
            $table->unsignedSmallInteger('frecuencia_respiratoria')->nullable(); // rpm

            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signos_vitales');
    }
};
