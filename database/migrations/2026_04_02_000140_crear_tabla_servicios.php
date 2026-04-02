<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: servicios
// Descripción: Catálogo de servicios/procedimientos que ofrece la IPS.
// Ejemplos: Consulta General, Ecografía Obstétrica, Toma de Muestras.
// Cada servicio define la duración esperada, lo que permite al sistema
// validar disponibilidad del médico al agendar una cita.
// -----------------------------------------------------------------------

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->onDelete('cascade');

            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->unsignedSmallInteger('duracion_minutos')->default(30); // Duración esperada de la consulta
            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->unique(['nombre', 'empresa_id'], 'unico_servicio_por_empresa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
