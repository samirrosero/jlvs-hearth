<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('icono_servicios_path')->nullable()->after('icono_horarios_path');
            $table->string('icono_convenios_path')->nullable()->after('icono_servicios_path');
            $table->string('icono_auditoria_path')->nullable()->after('icono_convenios_path');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'icono_servicios_path',
                'icono_convenios_path',
                'icono_auditoria_path',
            ]);
        });
    }
};
