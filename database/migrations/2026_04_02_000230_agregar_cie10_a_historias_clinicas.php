<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Migración: agrega codigo_cie10 a historias_clinicas
// El CIE-10 (Clasificación Internacional de Enfermedades) es obligatorio
// en historias clínicas colombianas. El médico selecciona el código
// y la descripción queda guardada directamente para no depender del
// catálogo si los códigos cambian en el futuro.
// -----------------------------------------------------------------------

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historias_clinicas', function (Blueprint $table) {
            $table->string('codigo_cie10', 10)->nullable()->after('diagnostico');
            $table->string('descripcion_cie10')->nullable()->after('codigo_cie10');
        });

        Schema::create('cie10', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();   // Ej: J00, A09, K29.7
            $table->string('descripcion');             // Ej: Rinofaringitis aguda (resfriado común)
            $table->string('categoria', 3)->index();   // Ej: J00 → J
        });
    }

    public function down(): void
    {
        Schema::table('historias_clinicas', function (Blueprint $table) {
            $table->dropColumn(['codigo_cie10', 'descripcion_cie10']);
        });
        Schema::dropIfExists('cie10');
    }
};
