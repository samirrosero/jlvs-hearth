<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Poblar la base de datos con los datos iniciales del sistema.
     *
     * Orden importante:
     *  1. Catálogos globales (no dependen de nada)
     *  2. Datos de prueba (dependen de los catálogos)
     */
    public function run(): void
    {
        // --- 1. Catálogos globales del sistema ---
        $this->call([
            RolSeeder::class,
            ModalidadCitaSeeder::class,
            EstadoCitaSeeder::class,
        ]);

        // --- 2. Datos de prueba (empresa demo con médicos, horarios y servicios) ---
        $this->call([
            EmpresaDemoSeeder::class,
        ]);
    }
}
