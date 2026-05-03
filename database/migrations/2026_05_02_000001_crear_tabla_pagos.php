<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            
            $table->decimal('monto', 12, 2);
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia', 'prepagada', 'seguro', 'empresarial']);
            $table->enum('estado', ['pendiente', 'pagado', 'reembolsado', 'cancelado'])->default('pendiente');
            $table->enum('tipo_pago', ['presencial', 'telemedicina']);
            $table->string('referencia', 100)->nullable(); // número transacción
            $table->timestamp('fecha_pago')->nullable();
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['empresa_id', 'estado']);
            $table->index(['cita_id']);
            $table->index(['paciente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
