<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: valoraciones
// Descripción: Permite al paciente calificar la atención recibida en una
// cita. Cada cita puede tener como máximo UNA valoración.
// La calificación va de 1 a 5 estrellas y admite un comentario opcional.
// -----------------------------------------------------------------------

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valoraciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cita_id')
                ->unique()                              // Una sola valoración por cita
                ->constrained('citas')
                ->onDelete('cascade');

            $table->foreignId('paciente_id')
                ->constrained('pacientes')
                ->onDelete('cascade');

            $table->tinyInteger('puntuacion');          // 1 a 5 estrellas
            $table->text('comentario')->nullable();     // Comentario libre del paciente

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valoraciones');
    }
};
