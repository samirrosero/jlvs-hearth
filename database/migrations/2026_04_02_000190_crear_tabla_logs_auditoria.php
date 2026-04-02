<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: logs_auditoria
// Descripción: Trazabilidad de acceso y modificación a datos sensibles.
// Exigido por la Resolución 1995 de 1999 del Ministerio de Salud de
// Colombia, que obliga a registrar quién accedió o modificó una historia
// clínica, cuándo y desde dónde.
//
// Solo tiene created_at (no updated_at) — los logs son inmutables.
// Se escribe automáticamente vía Observer, nunca por el usuario.
// -----------------------------------------------------------------------

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs_auditoria', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')
                ->nullable()                             // nullable: puede haber accesos sin sesión activa
                ->constrained('users')
                ->onDelete('set null');

            $table->foreignId('empresa_id')
                ->nullable()
                ->constrained('empresas')
                ->onDelete('set null');

            $table->enum('accion', ['ver', 'crear', 'actualizar', 'eliminar']);
            $table->string('modelo', 100);               // Nombre del modelo afectado (ej: HistoriaClinica)
            $table->unsignedBigInteger('modelo_id');     // ID del registro afectado
            $table->string('ip', 45)->nullable();        // IPv4 o IPv6
            $table->json('detalles')->nullable();        // Campos modificados (para actualizar/eliminar)

            // Solo created_at — los logs son inmutables
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs_auditoria');
    }
};
