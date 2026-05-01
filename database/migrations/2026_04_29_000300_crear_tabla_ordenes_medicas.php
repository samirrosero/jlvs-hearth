<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_medicas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('historia_clinica_id')
                ->constrained('historias_clinicas')
                ->onDelete('cascade');

            $table->foreignId('paciente_id')
                ->constrained('pacientes');

            // Tipo de orden: Laboratorio, Imagen diagnóstica, Procedimiento, Interconsulta, Otro
            $table->string('tipo');

            $table->text('descripcion');
            $table->text('instrucciones')->nullable();

            // Estado: pendiente → autorizada
            $table->string('estado')->default('pendiente');

            // Quién autorizó y cómo
            $table->timestamp('autorizado_en')->nullable();
            $table->string('autorizado_via')->nullable(); // 'presencial' | 'virtual'

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_medicas');
    }
};
