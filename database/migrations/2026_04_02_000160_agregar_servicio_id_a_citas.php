<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Migración: agrega servicio_id a la tabla citas
// Descripción: Vincula cada cita con el servicio/procedimiento que se va
// a realizar, permitiendo mostrar el catálogo de servicios al agendar y
// usar la duración del servicio para validar disponibilidad del médico.
//
// nullable: las citas existentes quedan sin servicio asignado.
// -----------------------------------------------------------------------

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->foreignId('servicio_id')
                ->nullable()
                ->after('portafolio_id')
                ->constrained('servicios')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['servicio_id']);
            $table->dropColumn('servicio_id');
        });
    }
};
