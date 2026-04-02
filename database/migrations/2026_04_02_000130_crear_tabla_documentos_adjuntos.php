<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: documentos_adjuntos
// Descripción: Almacena archivos digitales asociados a una historia
// clínica: resultados de laboratorio, imágenes diagnósticas, reportes,
// incapacidades, certificados médicos, etc.
//
// Los archivos físicos se guardan en el Storage de Laravel.
// Esta tabla solo guarda los metadatos y la ruta de acceso.
// -----------------------------------------------------------------------

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('documentos_adjuntos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('historia_clinica_id')    // Historia a la que pertenece este documento
                ->constrained('historias_clinicas')
                ->onDelete('cascade');

            $table->string('nombre_archivo');           // Nombre original del archivo (ej: 'resultado_hemograma.pdf')
            $table->string('ruta_almacenamiento');      // Ruta en el Storage de Laravel (ej: 'documentos/2026/resultado.pdf')
            $table->string('tipo_mime');                // Tipo de archivo (ej: 'application/pdf', 'image/jpeg')

            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_adjuntos');
    }
};
