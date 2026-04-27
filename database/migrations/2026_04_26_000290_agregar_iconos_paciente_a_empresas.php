<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('icono_pac_inicio_path')->nullable()->after('icono_card_total_path');
            $table->string('icono_pac_citas_path')->nullable()->after('icono_pac_inicio_path');
            $table->string('icono_pac_historial_path')->nullable()->after('icono_pac_citas_path');
            $table->string('icono_pac_perfil_path')->nullable()->after('icono_pac_historial_path');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'icono_pac_inicio_path',
                'icono_pac_citas_path',
                'icono_pac_historial_path',
                'icono_pac_perfil_path',
            ]);
        });
    }
};
