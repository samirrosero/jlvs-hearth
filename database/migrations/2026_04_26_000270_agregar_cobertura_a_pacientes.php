<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->foreignId('portafolio_id')
                ->nullable()
                ->after('empresa_id')
                ->constrained('portafolios')
                ->nullOnDelete();

            $table->string('nombre_aseguradora', 100)->nullable()->after('portafolio_id');
            $table->string('numero_poliza', 60)->nullable()->after('nombre_aseguradora');
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropForeign(['portafolio_id']);
            $table->dropColumn(['portafolio_id', 'nombre_aseguradora', 'numero_poliza']);
        });
    }
};
