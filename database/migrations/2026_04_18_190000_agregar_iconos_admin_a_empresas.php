<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// Agrega campos para iconos personalizados del panel administrativo
// Cada IPS puede subir sus propios iconos para el sidebar y dashboard
// -----------------------------------------------------------------------

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Iconos para el sidebar del admin
            $table->string('icono_dashboard_path')->nullable()->after('color_pdf');
            $table->string('icono_pacientes_path')->nullable()->after('icono_dashboard_path');
            $table->string('icono_medicos_path')->nullable()->after('icono_pacientes_path');
            $table->string('icono_reportes_path')->nullable()->after('icono_medicos_path');
            $table->string('icono_solicitudes_path')->nullable()->after('icono_reportes_path');
            $table->string('icono_identidad_path')->nullable()->after('icono_solicitudes_path');
            
            // Iconos para las tarjetas del dashboard (estadísticas)
            $table->string('icono_card_pacientes_path')->nullable()->after('icono_identidad_path');
            $table->string('icono_card_medicos_path')->nullable()->after('icono_card_pacientes_path');
            $table->string('icono_card_citas_path')->nullable()->after('icono_card_medicos_path');
            $table->string('icono_card_total_path')->nullable()->after('icono_card_citas_path');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'icono_dashboard_path',
                'icono_pacientes_path',
                'icono_medicos_path',
                'icono_reportes_path',
                'icono_solicitudes_path',
                'icono_identidad_path',
                'icono_card_pacientes_path',
                'icono_card_medicos_path',
                'icono_card_citas_path',
                'icono_card_total_path',
            ]);
        });
    }
};
