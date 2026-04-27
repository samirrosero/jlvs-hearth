<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Tabla: precios_servicio
// Descripción: Tarifas negociadas por portafolio (convenio/aseguradora).
// Cada IPS configura cuánto cobra por servicio según el tipo de cobertura
// del paciente (Particular, Prepagada, Póliza, Convenio empresarial).
// -----------------------------------------------------------------------

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precios_servicio', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->foreignId('servicio_id')
                ->constrained('servicios')
                ->cascadeOnDelete();

            $table->foreignId('portafolio_id')
                ->constrained('portafolios')
                ->cascadeOnDelete();

            $table->decimal('precio', 12, 2);

            $table->timestamps();

            $table->unique(['servicio_id', 'portafolio_id'], 'unico_precio_servicio_portafolio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precios_servicio');
    }
};
