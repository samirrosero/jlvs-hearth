<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('importaciones_masivas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('tipo', 30); // pacientes, medicos, gestores, administradores
            $table->string('nombre_archivo');
            $table->string('ruta_archivo');
            $table->unsignedInteger('total_filas')->default(0);
            $table->unsignedInteger('procesadas')->default(0);
            $table->unsignedInteger('exitosas')->default(0);
            $table->unsignedInteger('fallidas')->default(0);
            $table->enum('estado', ['pendiente', 'procesando', 'completada', 'fallida'])->default('pendiente');
            $table->boolean('enviar_correos')->default(true);
            $table->json('usuarios_creados')->nullable();
            $table->json('errores')->nullable();
            $table->timestamp('iniciado_en')->nullable();
            $table->timestamp('finalizado_en')->nullable();
            $table->text('mensaje_error')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'user_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importaciones_masivas');
    }
};
