<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: portafolios
// Descripción: Representa los convenios o tipos de cobertura médica que
// maneja cada IPS (ej: EPS Sura, Medicina Prepagada, Particular).
// Cada empresa configura su propio portafolio de servicios.
// -----------------------------------------------------------------------

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('portafolios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')             // IPS dueña de este convenio
                ->constrained('empresas')
                ->onDelete('cascade');

            $table->string('nombre_convenio');          // Nombre del convenio (ej: 'EPS Sura', 'Particular')
            $table->text('descripcion')->nullable();    // Descripción adicional del convenio
            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('portafolios');
    }
};
