<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: horarios_medico
// Descripción: Define la disponibilidad semanal de cada médico por día.
// Un médico puede tener varios bloques horarios el mismo día (ej: mañana
// y tarde). Se usa para validar que una cita se agende dentro de la
// disponibilidad real del médico.
//
// dia_semana: 0 = domingo, 1 = lunes, ..., 6 = sábado (estándar ISO/PHP)
// -----------------------------------------------------------------------

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_medico', function (Blueprint $table) {
            $table->id();

            $table->foreignId('medico_id')
                ->constrained('medicos')
                ->onDelete('cascade');

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->onDelete('cascade');

            $table->unsignedTinyInteger('dia_semana');   // 0=domingo … 6=sábado
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_medico');
    }
};
