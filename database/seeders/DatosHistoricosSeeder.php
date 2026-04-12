<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\Empresa;
use App\Models\EjecucionCita;
use App\Models\EstadoCita;
use App\Models\HistoriaClinica;
use App\Models\Medico;
use App\Models\ModalidadCita;
use App\Models\Paciente;
use App\Models\Portafolio;
use App\Models\Rol;
use App\Models\SignosVitales;
use App\Models\User;
use App\Models\Valoracion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder de fondo — datos históricos de los últimos 6 meses.
 * Solo genera pacientes y citas ficticias para que el dashboard se vea lleno.
 *
 * Ejecutar: php artisan db:seed --class=DatosHistoricosSeeder
 */
class DatosHistoricosSeeder extends Seeder
{
    public function run(): void
    {
        $empresa    = Empresa::where('nit', '900123456-1')->firstOrFail();
        $medicos    = Medico::where('empresa_id', $empresa->id)->get();
        $estadoPendiente = EstadoCita::where('nombre', 'Pendiente')->first();
        $estadoAtendida  = EstadoCita::where('nombre', 'Atendida')->first();
        $estadoCancelada = EstadoCita::where('nombre', 'Cancelada')->first();
        $modalidad  = ModalidadCita::where('nombre', 'Presencial')->first();
        $rolPaciente = Rol::where('nombre', 'paciente')->first();

        $portafolio = Portafolio::where('empresa_id', $empresa->id)->first();

        // ── Diagnósticos ficticios con CIE-10 ────────────────────────
        $diagnosticos = [
            ['diagnostico' => 'Rinofaringitis aguda. Reposo relativo, abundante hidratación.', 'codigo' => 'J00',   'descripcion' => 'Rinofaringitis aguda (resfriado común)'],
            ['diagnostico' => 'Hipertensión esencial en control. Ajuste de medicación antihipertensiva.', 'codigo' => 'I10', 'descripcion' => 'Hipertensión esencial (primaria)'],
            ['diagnostico' => 'Diabetes mellitus tipo 2 sin complicaciones. Control glucémico adecuado.', 'codigo' => 'E11.9', 'descripcion' => 'Diabetes mellitus tipo 2 sin complicaciones'],
            ['diagnostico' => 'Gastritis no especificada. Se formula protector gástrico.', 'codigo' => 'K29.7', 'descripcion' => 'Gastritis, no especificada'],
            ['diagnostico' => 'Lumbago no especificado. Fisioterapia y analgésicos.', 'codigo' => 'M54.5', 'descripcion' => 'Lumbago no especificado'],
            ['diagnostico' => 'Episodio depresivo moderado. Se inicia manejo farmacológico.', 'codigo' => 'F32.9', 'descripcion' => 'Episodio depresivo, no especificado'],
            ['diagnostico' => 'Infección de vías urinarias. Se formula antibiótico.', 'codigo' => 'N39.0', 'descripcion' => 'Infección de vías urinarias, sitio no especificado'],
            ['diagnostico' => 'Cefalea tensional. Se recomienda manejo del estrés.', 'codigo' => 'G44.2', 'descripcion' => 'Cefalea tensional'],
            ['diagnostico' => 'Asma, consulta de control. Buena respuesta al manejo actual.', 'codigo' => 'J45.9', 'descripcion' => 'Asma, no especificada'],
            ['diagnostico' => 'Hipotiroidismo en control. TSH en rango terapéutico.', 'codigo' => 'E03.9', 'descripcion' => 'Hipotiroidismo, no especificado'],
        ];

        // ── Pacientes ficticios ────────────────────────────────────────
        $nombresPacientes = [
            ['nombre' => 'Carlos Andrés Muñoz Salcedo',   'id' => '1090401234', 'sexo' => 'M', 'correo' => 'carlos.munoz.demo@mailinator.com'],
            ['nombre' => 'María Fernanda López Castillo',  'id' => '1107802345', 'sexo' => 'F', 'correo' => 'mflopez.demo@mailinator.com'],
            ['nombre' => 'Juan Pablo Gómez Restrepo',      'id' => '1094503456', 'sexo' => 'M', 'correo' => null],
            ['nombre' => 'Sandra Patricia Ortiz Vargas',   'id' => '31204567',   'sexo' => 'F', 'correo' => 'sandraortiz.demo@mailinator.com'],
            ['nombre' => 'Luis Eduardo Castaño Mejía',     'id' => '79455678',   'sexo' => 'M', 'correo' => null],
            ['nombre' => 'Valentina Herrera Ospina',       'id' => '1005906789', 'sexo' => 'F', 'correo' => 'valeria.demo@mailinator.com'],
            ['nombre' => 'Andrés Felipe Quintero Ríos',    'id' => '1113207890', 'sexo' => 'M', 'correo' => null],
            ['nombre' => 'Claudia Milena Torres Bedoya',   'id' => '43418901',   'sexo' => 'F', 'correo' => 'claudiatorres.demo@mailinator.com'],
            ['nombre' => 'Diego Alejandro Vargas Mora',    'id' => '1092409012', 'sexo' => 'M', 'correo' => null],
            ['nombre' => 'Natalia Andrea Ramírez Peña',    'id' => '1106510123', 'sexo' => 'F', 'correo' => 'nramírez.demo@mailinator.com'],
            ['nombre' => 'Jorge Iván Suárez Londoño',      'id' => '71621234',   'sexo' => 'M', 'correo' => null],
            ['nombre' => 'Paola Andrea Ríos Acosta',       'id' => '43432345',   'sexo' => 'F', 'correo' => null],
            ['nombre' => 'Sebastián Castro Gutiérrez',     'id' => '1095343456', 'sexo' => 'M', 'correo' => null],
            ['nombre' => 'Alejandra Soto Cárdenas',        'id' => '1098554567', 'sexo' => 'F', 'correo' => null],
            ['nombre' => 'Ricardo Patiño Álvarez',         'id' => '79765678',   'sexo' => 'M', 'correo' => null],
            ['nombre' => 'Lina Marcela Echeverri Mora',    'id' => '31376789',   'sexo' => 'F', 'correo' => null],
            ['nombre' => 'Camilo Ernesto Ossa Arango',     'id' => '1091987890', 'sexo' => 'M', 'correo' => null],
            ['nombre' => 'Gloria Inés Zapata Mejía',       'id' => '25498901',   'sexo' => 'F', 'correo' => null],
            ['nombre' => 'Felipe Augusto Hoyos Ríos',      'id' => '1002709012', 'sexo' => 'M', 'correo' => null],
            ['nombre' => 'Marcela Johana Álvarez Cano',    'id' => '43210123',   'sexo' => 'F', 'correo' => null],
        ];

        $pacientes = [];
        $fechasNacimiento = [
            '1985-03-12', '1990-07-25', '1978-11-08', '1995-02-14', '1968-09-30',
            '2001-05-20', '1975-12-01', '1988-06-17', '1993-04-03', '1970-08-22',
            '1983-01-15', '1997-10-09', '1965-07-28', '2000-03-31', '1979-11-14',
            '1992-06-05', '1987-09-18', '1973-02-27', '1998-12-11', '1962-04-06',
        ];

        foreach ($nombresPacientes as $i => $datos) {
            $paciente = Paciente::firstOrCreate(
                ['identificacion' => $datos['id'], 'empresa_id' => $empresa->id],
                [
                    'empresa_id'       => $empresa->id,
                    'usuario_id'       => null,
                    'nombre_completo'  => $datos['nombre'],
                    'fecha_nacimiento' => $fechasNacimiento[$i],
                    'sexo'             => $datos['sexo'],
                    'telefono'         => '310' . rand(1000000, 9999999),
                    'correo'           => $datos['correo'],
                    'direccion'        => 'Cali, Valle del Cauca',
                    'identificacion'   => $datos['id'],
                ]
            );
            // Distribuir la creación en el tiempo para que el gráfico de "nuevos pacientes por mes" sea variado
            $paciente->created_at = Carbon::now()->subMonths(rand(0, 6))->subDays(rand(0, 28));
            $paciente->save();

            $pacientes[] = $paciente;
        }

        // ── Citas históricas (últimos 6 meses) ───────────────────────
        $horas = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'];

        $citasCreadas    = 0;
        $historiasCreadas = 0;

        for ($mesAtras = 6; $mesAtras >= 0; $mesAtras--) {
            $citasEsteMes = rand(12, 22);

            for ($c = 0; $c < $citasEsteMes; $c++) {
                $fechaBase = Carbon::now()->subMonths($mesAtras)->startOfMonth();
                $diasEnMes = $fechaBase->daysInMonth;
                $diaCita   = rand(1, $diasEnMes - 1);
                $fecha     = $fechaBase->copy()->addDays($diaCita);

                // Evitar fines de semana
                if ($fecha->isWeekend()) {
                    $fecha->addDays(1);
                    if ($fecha->isWeekend()) {
                        $fecha->addDays(1);
                    }
                }

                $medico   = $medicos->random();
                $paciente = collect($pacientes)->random();
                $hora     = $horas[array_rand($horas)];

                // Estado: si la cita es pasada → atendida (80%) o cancelada (20%)
                // Si es futura → pendiente
                $esPasada = $fecha->isPast();
                if ($esPasada) {
                    $estado = rand(1, 10) <= 8 ? $estadoAtendida : $estadoCancelada;
                } else {
                    $estado = $estadoPendiente;
                }

                $cita = Cita::firstOrCreate(
                    [
                        'empresa_id'  => $empresa->id,
                        'medico_id'   => $medico->id,
                        'paciente_id' => $paciente->id,
                        'fecha'       => $fecha->toDateString(),
                        'hora'        => $hora,
                    ],
                    [
                        'empresa_id'    => $empresa->id,
                        'medico_id'     => $medico->id,
                        'paciente_id'   => $paciente->id,
                        'estado_id'     => $estado->id,
                        'modalidad_id'  => $modalidad->id,
                        'portafolio_id' => $portafolio?->id,
                        'fecha'         => $fecha->toDateString(),
                        'hora'          => $hora,
                        'activo'        => $estado->nombre !== 'Cancelada',
                    ]
                );

                $cita->created_at = $fecha->copy()->subDays(rand(1, 7));
                $cita->save();
                $citasCreadas++;

                // Crear ejecución + historia clínica para citas atendidas
                if ($estado->nombre === 'Atendida' && !$cita->ejecucion) {
                    $inicioParsed  = Carbon::parse($fecha->toDateString() . ' ' . $hora);
                    $duracion      = [20, 25, 30, 35, 40][array_rand([20, 25, 30, 35, 40])];
                    $fin           = $inicioParsed->copy()->addMinutes($duracion);

                    $ejecucion = EjecucionCita::create([
                        'cita_id'          => $cita->id,
                        'inicio_atencion'  => $inicioParsed,
                        'fin_atencion'     => $fin,
                        'duracion_minutos' => $duracion,
                    ]);

                    $dx = $diagnosticos[array_rand($diagnosticos)];

                    HistoriaClinica::create([
                        'ejecucion_cita_id' => $ejecucion->id,
                        'paciente_id'       => $paciente->id,
                        'motivo_consulta'   => 'Consulta por molestias generales y seguimiento de condición crónica.',
                        'enfermedad_actual' => 'Paciente refiere cuadro de ' . rand(3, 15) . ' días de evolución con sintomatología compatible con el diagnóstico.',
                        'antecedentes'      => ['patológicos' => 'HTA', 'quirúrgicos' => 'Ninguno'],
                        'diagnostico'       => $dx['diagnostico'],
                        'codigo_cie10'      => $dx['codigo'],
                        'descripcion_cie10' => $dx['descripcion'],
                        'plan_tratamiento'  => 'Se formula medicación según diagnóstico. Control en 15 días.',
                        'evaluacion'        => 'Paciente en buen estado general.',
                        'observaciones'     => null,
                    ]);

                    // Signos vitales
                    SignosVitales::create([
                        'ejecucion_cita_id'   => $ejecucion->id,
                        'paciente_id'         => $paciente->id,
                        'peso_kg'             => rand(50, 100) + (rand(0, 9) / 10),
                        'talla_cm'            => rand(150, 185),
                        'presion_sistolica'   => rand(110, 145),
                        'presion_diastolica'  => rand(65, 95),
                        'temperatura_c'       => 36 + (rand(0, 15) / 10),
                        'frecuencia_cardiaca' => rand(60, 100),
                        'saturacion_oxigeno'  => rand(94, 99),
                        'frecuencia_respiratoria' => rand(14, 20),
                    ]);

                    $historiasCreadas++;

                    // Valoración (60% de las citas atendidas)
                    if (rand(1, 10) <= 6 && !$cita->valoracion) {
                        Valoracion::create([
                            'cita_id'    => $cita->id,
                            'paciente_id' => $paciente->id,
                            'puntuacion' => rand(3, 5),
                            'comentario' => null,
                        ]);
                    }
                }
            }
        }

        $this->command->info("✓ Datos históricos creados:");
        $this->command->info("  Pacientes ficticios: " . count($pacientes));
        $this->command->info("  Citas generadas: {$citasCreadas}");
        $this->command->info("  Historias clínicas: {$historiasCreadas}");
    }
}
