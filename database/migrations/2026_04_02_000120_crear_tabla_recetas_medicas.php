<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: recetas_medicas
// Descripción: Almacena las prescripciones médicas generadas por el
// médico al finalizar una consulta. Están vinculadas a la historia
// clínica correspondiente.
//
// Una historia clínica puede generar UNA o VARIAS recetas médicas
// (ej: una para medicamentos y otra para exámenes de laboratorio).
// -----------------------------------------------------------------------

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('recetas_medicas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('historia_clinica_id')    // Historia a la que pertenece esta receta
                ->constrained('historias_clinicas')
                ->onDelete('cascade');

            $table->text('medicamentos');               // Lista de medicamentos prescritos (nombre, dosis, frecuencia)
            $table->text('indicaciones');               // Instrucciones de uso para el paciente

            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('recetas_medicas');
    }
};
