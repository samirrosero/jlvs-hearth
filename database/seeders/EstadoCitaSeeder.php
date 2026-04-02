<?php

namespace Database\Seeders;

use App\Models\EstadoCita;
use Illuminate\Database\Seeder;

class EstadoCitaSeeder extends Seeder
{
    /**
     * Poblar la tabla de estados de cita.
     * Los colores hex se usan para mostrar visualmente el estado en el calendario.
     */
    public function run(): void
    {
        $estados = [
            ['nombre' => 'Pendiente',   'color_hex' => '#FFA500'], // Naranja
            ['nombre' => 'Confirmada',  'color_hex' => '#007BFF'], // Azul
            ['nombre' => 'Atendida',    'color_hex' => '#28A745'], // Verde
            ['nombre' => 'Cancelada',   'color_hex' => '#DC3545'], // Rojo
            ['nombre' => 'No asistió',  'color_hex' => '#6C757D'], // Gris
        ];

        foreach ($estados as $estado) {
            EstadoCita::firstOrCreate(['nombre' => $estado['nombre']], $estado);
        }
    }
}
