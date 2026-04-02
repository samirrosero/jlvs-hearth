<?php

namespace Database\Seeders;

use App\Models\ModalidadCita;
use Illuminate\Database\Seeder;

class ModalidadCitaSeeder extends Seeder
{
    /**
     * Poblar la tabla de modalidades de cita.
     */
    public function run(): void
    {
        $modalidades = [
            'Presencial',
            'Telemedicina',
            'Domiciliaria',
        ];

        foreach ($modalidades as $nombre) {
            ModalidadCita::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
