<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Poblar la tabla de roles con los perfiles del sistema.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre'      => 'administrador',
                'descripcion' => 'Acceso total al sistema de su empresa. Gestiona usuarios, médicos y configuración.',
            ],
            [
                'nombre'      => 'medico',
                'descripcion' => 'Gestiona su agenda de citas y genera historias clínicas y recetas médicas.',
            ],
            [
                'nombre'      => 'gestor_citas',
                'descripcion' => 'Programa, confirma y cancela citas. Gestiona el agendamiento de la IPS.',
            ],
            [
                'nombre'      => 'paciente',
                'descripcion' => 'Acceso restringido a su propia información: citas e historial clínico.',
            ],
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(['nombre' => $rol['nombre']], $rol);
        }
    }
}
