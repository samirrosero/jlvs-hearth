<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: lista_espera
// Pacientes que solicitan cita cuando no hay disponibilidad ese día.
// El gestor los registra y los contacta si se libera un slot.
// -----------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lista_espera', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')->onDelete('cascade');

            $table->foreignId('paciente_id')
                ->constrained('pacientes')->onDelete('cascade');

            $table->foreignId('medico_id')
                ->nullable()->constrained('medicos')->onDelete('set null');

            $table->foreignId('servicio_id')
                ->nullable()->constrained('servicios')->onDelete('set null');

            $table->date('fecha_solicitada');

            $table->enum('estado', ['esperando', 'asignado', 'descartado'])
                ->default('esperando');

            $table->text('notas')->nullable();

            $table->foreignId('cita_id')
                ->nullable()->constrained('citas')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lista_espera');
    }
};
