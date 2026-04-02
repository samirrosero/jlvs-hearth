<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: estados_cita
// Descripción: Catálogo de los posibles estados del ciclo de vida de
// una cita médica. Es una tabla de referencia global.
//
// Valores iniciales esperados (ver Seeder):
//   - Pendiente     → cita agendada, en espera de confirmación
//   - Confirmada    → cita confirmada por la IPS
//   - Atendida      → paciente fue atendido
//   - Cancelada     → cita cancelada por paciente o IPS
//   - No asistió    → paciente no se presentó
// -----------------------------------------------------------------------

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('estados_cita', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();         // Nombre del estado (ej: 'Pendiente')
            $table->string('color_hex')->nullable();    // Color para mostrar en el calendario del frontend
            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('estados_cita');
    }
};
