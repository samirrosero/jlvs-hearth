<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: modalidades_cita
// Descripción: Catálogo de modalidades de atención disponibles.
// Es una tabla de referencia global (no por empresa).
//
// Valores iniciales esperados (ver Seeder):
//   - Presencial
//   - Telemedicina
//   - Domiciliaria
// -----------------------------------------------------------------------

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('modalidades_cita', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();         // Nombre de la modalidad (ej: 'Presencial')
            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('modalidades_cita');
    }
};
