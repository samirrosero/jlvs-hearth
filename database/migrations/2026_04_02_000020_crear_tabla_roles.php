<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: roles
// Descripción: Define los perfiles de acceso del sistema.
// Los roles son globales (no por empresa) ya que los permisos son los
// mismos para todos los clientes del software.
//
// Roles esperados:
//   - administrador  → acceso total al sistema de su empresa
//   - medico         → gestiona citas e historias clínicas
//   - gestor_citas   → programa y administra la agenda
//   - paciente       → consulta sus citas e historial (acceso restringido)
// -----------------------------------------------------------------------

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();          // Identificador único del rol (ej: 'medico')
            $table->string('descripcion')->nullable();   // Descripción legible del rol
            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
