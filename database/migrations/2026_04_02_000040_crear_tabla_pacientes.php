<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: pacientes
// Descripción: Almacena los datos personales de cada paciente registrado
// en una IPS. El paciente puede o no tener acceso al sistema (usuario_id
// es opcional). La identificación es única POR EMPRESA, permitiendo que
// la misma persona pueda ser paciente en dos IPS distintas.
// -----------------------------------------------------------------------

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();

            // --- Relaciones ---
            $table->foreignId('usuario_id')             // Usuario del sistema (si el paciente tiene login)
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->foreignId('empresa_id')             // IPS a la que pertenece este paciente
                ->constrained('empresas')
                ->onDelete('cascade');

            // --- Datos personales ---
            $table->string('nombre_completo');          // Nombre y apellidos completos
            $table->date('fecha_nacimiento');           // Usamos fecha en lugar de edad (la edad cambia)
            $table->enum('sexo', ['M', 'F', 'Otro']);  // Sexo biológico del paciente
            $table->string('telefono');                 // Teléfono de contacto
            $table->string('correo')->nullable();       // Correo electrónico del paciente
            $table->string('direccion')->nullable();    // Dirección de residencia
            $table->string('identificacion');           // Cédula o documento de identidad

            $table->timestamps();

            // La identificación es única por empresa (un mismo paciente puede
            // estar en dos IPS distintas sin generar conflicto)
            $table->unique(['identificacion', 'empresa_id'], 'unico_paciente_por_empresa');
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
