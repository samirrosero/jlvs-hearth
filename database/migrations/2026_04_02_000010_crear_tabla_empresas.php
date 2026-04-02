<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: empresas
// Descripción: Almacena cada IPS cliente que adquiere el software JLVS.
// Esta tabla es la raíz del modelo multi-tenant: todos los datos del
// sistema están asociados a una empresa específica.
// -----------------------------------------------------------------------

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nit')->unique();             // NIT de la IPS (identificador tributario único)
            $table->string('nombre');                    // Nombre comercial de la IPS
            $table->string('telefono')->nullable();      // Teléfono de contacto principal
            $table->string('correo')->nullable();        // Correo electrónico institucional
            $table->string('direccion')->nullable();     // Dirección física de la sede principal
            $table->string('ciudad')->nullable();        // Ciudad donde opera la IPS
            $table->boolean('activo')->default(true);   // Permite desactivar una empresa sin borrarla
            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
