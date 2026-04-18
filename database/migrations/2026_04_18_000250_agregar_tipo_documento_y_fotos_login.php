<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── tipo_documento en users ───────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->string('tipo_documento')->default('CC')->after('identificacion');
        });

        // ── tipo_documento en pacientes ───────────────────────────
        Schema::table('pacientes', function (Blueprint $table) {
            $table->string('tipo_documento')->default('CC')->after('identificacion');
            $table->string('apellidos')->nullable()->after('nombre_completo');
        });

        // ── imágenes de las páginas de login y registro ───────────
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('imagen_login_path')->nullable()->after('favicon_path');
            $table->string('imagen_registro_path')->nullable()->after('imagen_login_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('tipo_documento'));
        Schema::table('pacientes', fn (Blueprint $t) => $t->dropColumn(['tipo_documento', 'apellidos']));
        Schema::table('empresas', fn (Blueprint $t) => $t->dropColumn(['imagen_login_path', 'imagen_registro_path']));
    }
};
