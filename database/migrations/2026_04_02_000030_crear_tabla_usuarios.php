<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: users  (nombre requerido por el sistema de autenticación Laravel)
// Descripción: Almacena las credenciales de acceso de todos los usuarios
// del sistema. Cada usuario pertenece a UNA empresa (multi-tenant) y
// tiene UN rol que define sus permisos.
//
// IMPORTANTE: Esta migración debe ejecutarse DESPUÉS de 'empresas' y
// 'roles', por eso tiene un número de orden superior (000030).
// -----------------------------------------------------------------------

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // --- Relaciones obligatorias ---
            $table->foreignId('empresa_id')             // IPS a la que pertenece este usuario
                ->constrained('empresas')
                ->onDelete('cascade');

            $table->foreignId('rol_id')                 // Rol que define sus permisos
                ->constrained('roles');

            // --- Datos de identidad ---
            $table->string('nombre');                   // Nombre completo del usuario
            $table->string('email')->unique();          // Correo (usado para iniciar sesión)
            $table->string('identificacion')->unique(); // Cédula o documento de identidad
            $table->string('password');                 // Contraseña cifrada (bcrypt)

            // --- Campos de Laravel Auth ---
            $table->timestamp('email_verified_at')->nullable();   // Fecha de verificación del correo (nombre requerido por Laravel)
            $table->rememberToken();                    // Token para "recordarme"
            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
