<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Iconos exclusivos del panel del médico
            $table->string('icono_medico_dashboard_path')->nullable()->after('icono_pac_perfil_path');
            $table->string('icono_medico_citas_path')->nullable()->after('icono_medico_dashboard_path');
            $table->string('icono_medico_pacientes_path')->nullable()->after('icono_medico_citas_path');
            // Icono de Horarios para el panel admin
            $table->string('icono_horarios_path')->nullable()->after('icono_medico_pacientes_path');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'icono_medico_dashboard_path',
                'icono_medico_citas_path',
                'icono_medico_pacientes_path',
                'icono_horarios_path',
            ]);
        });
    }
};
