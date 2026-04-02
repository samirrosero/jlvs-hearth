<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\HorarioMedico;
use App\Models\Medico;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Datos de prueba para una IPS demo.
 * Crea: empresa, admin, 2 médicos con horarios, catálogo de servicios.
 *
 * Ejecutar: php artisan db:seed --class=EmpresaDemoSeeder
 */
class EmpresaDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Empresa demo ──────────────────────────────────────────────
        $empresa = Empresa::firstOrCreate(
            ['nit' => '900123456-1'],
            [
                'nombre'    => 'Clínica Demo JLVS',
                'telefono'  => '6017001234',
                'direccion' => 'Calle 100 # 15-20, Bogotá',
                'correo'    => 'contacto@clinicademo.co',
                'activo'    => true,
            ]
        );

        // ── Roles ────────────────────────────────────────────────────
        $rolAdmin  = Rol::where('nombre', 'administrador')->first();
        $rolMedico = Rol::where('nombre', 'medico')->first();

        // ── Usuario administrador ────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@clinicademo.co'],
            [
                'nombre'         => 'Administrador Demo',
                'identificacion' => '1000000001',
                'password'       => Hash::make('password'),
                'rol_id'         => $rolAdmin->id,
                'empresa_id'     => $empresa->id,
            ]
        );

        // ── Médicos ──────────────────────────────────────────────────
        $usuarioMedico1 = User::firstOrCreate(
            ['email' => 'dra.garcia@clinicademo.co'],
            [
                'nombre'         => 'Dra. Laura García',
                'identificacion' => '1000000002',
                'password'       => Hash::make('password'),
                'rol_id'         => $rolMedico->id,
                'empresa_id'     => $empresa->id,
            ]
        );

        $medico1 = Medico::firstOrCreate(
            ['registro_medico' => 'RM-12345'],
            [
                'usuario_id'   => $usuarioMedico1->id,
                'empresa_id'   => $empresa->id,
                'especialidad' => 'Medicina General',
            ]
        );

        $usuarioMedico2 = User::firstOrCreate(
            ['email' => 'dr.torres@clinicademo.co'],
            [
                'nombre'         => 'Dr. Andrés Torres',
                'identificacion' => '1000000003',
                'password'       => Hash::make('password'),
                'rol_id'         => $rolMedico->id,
                'empresa_id'     => $empresa->id,
            ]
        );

        $medico2 = Medico::firstOrCreate(
            ['registro_medico' => 'RM-67890'],
            [
                'usuario_id'     => $usuarioMedico2->id,
                'empresa_id'     => $empresa->id,
                'especialidad'   => 'Pediatría',
            ]
        );

        // ── Horarios de médicos ──────────────────────────────────────
        // Dra. García: lunes a viernes 8:00 – 12:00 y 14:00 – 18:00
        // Dr. Torres:  lunes, miércoles, viernes 9:00 – 13:00
        $horarios = [
            // Dra. García — mañanas (lun-vie = días 1..5)
            ...collect([1, 2, 3, 4, 5])->map(fn ($dia) => [
                'medico_id'   => $medico1->id,
                'empresa_id'  => $empresa->id,
                'dia_semana'  => $dia,
                'hora_inicio' => '08:00',
                'hora_fin'    => '12:00',
                'activo'      => true,
            ])->all(),
            // Dra. García — tardes (lun-vie)
            ...collect([1, 2, 3, 4, 5])->map(fn ($dia) => [
                'medico_id'   => $medico1->id,
                'empresa_id'  => $empresa->id,
                'dia_semana'  => $dia,
                'hora_inicio' => '14:00',
                'hora_fin'    => '18:00',
                'activo'      => true,
            ])->all(),
            // Dr. Torres — lun, mié, vie
            ...collect([1, 3, 5])->map(fn ($dia) => [
                'medico_id'   => $medico2->id,
                'empresa_id'  => $empresa->id,
                'dia_semana'  => $dia,
                'hora_inicio' => '09:00',
                'hora_fin'    => '13:00',
                'activo'      => true,
            ])->all(),
        ];

        foreach ($horarios as $horario) {
            HorarioMedico::firstOrCreate(
                [
                    'medico_id'  => $horario['medico_id'],
                    'dia_semana' => $horario['dia_semana'],
                    'hora_inicio' => $horario['hora_inicio'],
                ],
                $horario
            );
        }

        // ── Servicios / Procedimientos ───────────────────────────────
        $servicios = [
            ['nombre' => 'Consulta Medicina General',  'duracion_minutos' => 20, 'descripcion' => 'Consulta externa de medicina general.'],
            ['nombre' => 'Consulta Pediatría',          'duracion_minutos' => 20, 'descripcion' => 'Consulta externa pediátrica.'],
            ['nombre' => 'Control Prenatal',            'duracion_minutos' => 30, 'descripcion' => 'Seguimiento de embarazo.'],
            ['nombre' => 'Toma de Muestras',            'duracion_minutos' => 15, 'descripcion' => 'Extracción de sangre y muestras de laboratorio.'],
            ['nombre' => 'Electrocardiograma',          'duracion_minutos' => 30, 'descripcion' => 'Registro de la actividad eléctrica del corazón.'],
            ['nombre' => 'Ecografía Abdominal',         'duracion_minutos' => 40, 'descripcion' => 'Ecografía de órganos abdominales.'],
            ['nombre' => 'Vacunación',                  'duracion_minutos' => 15, 'descripcion' => 'Aplicación de vacunas del PAI y otras.'],
        ];

        foreach ($servicios as $servicio) {
            Servicio::firstOrCreate(
                ['nombre' => $servicio['nombre'], 'empresa_id' => $empresa->id],
                array_merge($servicio, ['empresa_id' => $empresa->id, 'activo' => true])
            );
        }

        $this->command->info('✓ Empresa demo creada: Clínica Demo JLVS');
        $this->command->info('  admin@clinicademo.co / password');
        $this->command->info('  dra.garcia@clinicademo.co / password');
        $this->command->info('  dr.torres@clinicademo.co / password');
    }
}
