<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Iconos nuevos del sidebar admin
            $table->string('icono_valoraciones_path')->nullable()->after('icono_auditoria_path');
            $table->string('icono_importar_path')->nullable()->after('icono_valoraciones_path');

            // Icono del panel gestor - Recepción / Cobro
            $table->string('icono_gestor_recepcion_path')->nullable()->after('icono_gestor_pacientes_path');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'icono_valoraciones_path',
                'icono_importar_path',
                'icono_gestor_recepcion_path',
            ]);
        });
    }
};
