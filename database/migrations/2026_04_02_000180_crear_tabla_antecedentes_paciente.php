<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: antecedentes_paciente
// Descripción: Historial médico permanente del paciente, separado por tipo.
// A diferencia del campo JSON 'antecedentes' en historias_clinicas (que
// recoge lo observado en esa consulta), esta tabla guarda el perfil médico
// acumulado del paciente a lo largo del tiempo.
//
// Tipos: personal, familiar, quirurgico, alergico, farmacologico, otros
// -----------------------------------------------------------------------

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antecedentes_paciente', function (Blueprint $table) {
            $table->id();

            $table->foreignId('paciente_id')
                ->constrained('pacientes')
                ->onDelete('cascade');

            $table->enum('tipo', [
                'personal',
                'familiar',
                'quirurgico',
                'alergico',
                'farmacologico',
                'otros',
            ]);

            $table->text('descripcion');
            $table->boolean('activo')->default(true);   // Permite desactivar sin borrar

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antecedentes_paciente');
    }
};
