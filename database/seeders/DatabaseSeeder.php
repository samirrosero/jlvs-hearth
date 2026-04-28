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
            Cie10Seeder::class,
        ]);

        // --- 2. Empresa demo con médicos, horarios y servicios ---
        $this->call([
            EmpresaDemoSeeder::class,
        ]);

        // --- 3. Paciente demo con historia clínica completa ---
        $this->call([
            PacienteDemoSeeder::class,
        ]);

        // --- 4. Datos históricos de fondo (últimos 6 meses) ---
        // Genera pacientes y citas ficticias para que el dashboard se vea lleno en la demo.
        // Los usuarios del equipo se crean en vivo durante el sustento.
        $this->call([
            DatosHistoricosSeeder::class,
        ]);
    }
}
