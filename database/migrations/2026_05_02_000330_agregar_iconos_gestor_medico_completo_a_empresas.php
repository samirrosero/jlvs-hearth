<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Iconos del panel del gestor de citas (6 ítems del sidebar)
            $table->string('icono_gestor_dashboard_path')->nullable()->after('icono_auditoria_path');
            $table->string('icono_gestor_nueva_cita_path')->nullable()->after('icono_gestor_dashboard_path');
            $table->string('icono_gestor_citas_path')->nullable()->after('icono_gestor_nueva_cita_path');
            $table->string('icono_gestor_espera_path')->nullable()->after('icono_gestor_citas_path');
            $table->string('icono_gestor_registrar_path')->nullable()->after('icono_gestor_espera_path');
            $table->string('icono_gestor_pacientes_path')->nullable()->after('icono_gestor_registrar_path');

            // Iconos faltantes del panel del médico (4 ítems con SVG inline)
            $table->string('icono_medico_agenda_path')->nullable()->after('icono_gestor_pacientes_path');
            $table->string('icono_medico_horario_path')->nullable()->after('icono_medico_agenda_path');
            $table->string('icono_medico_ordenes_path')->nullable()->after('icono_medico_horario_path');
            $table->string('icono_medico_perfil_path')->nullable()->after('icono_medico_ordenes_path');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'icono_gestor_dashboard_path',
                'icono_gestor_nueva_cita_path',
                'icono_gestor_citas_path',
                'icono_gestor_espera_path',
                'icono_gestor_registrar_path',
                'icono_gestor_pacientes_path',
                'icono_medico_agenda_path',
                'icono_medico_horario_path',
                'icono_medico_ordenes_path',
                'icono_medico_perfil_path',
            ]);
        });
    }
};
