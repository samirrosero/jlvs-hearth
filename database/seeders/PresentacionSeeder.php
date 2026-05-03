<?php

namespace Database\Seeders;

use App\Models\AntecedentesPaciente;
use App\Models\Cita;
use App\Models\EjecucionCita;
use App\Models\Empresa;
use App\Models\EstadoCita;
use App\Models\HistoriaClinica;
use App\Models\ListaEspera;
use App\Models\LogAuditoria;
use App\Models\Medico;
use App\Models\ModalidadCita;
use App\Models\OrdenMedica;
use App\Models\Paciente;
use App\Models\Portafolio;
use App\Models\RecetaMedica;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\SignosVitales;
use App\Models\User;
use App\Models\Valoracion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Datos completos para la presentación del 3-4-5 y 21 de mayo.
 *
 * Usuarios existentes utilizados:
 * - 1098765432: Paciente Carlos Mendoza (ya existe)
 * - 3333333: Gestor (creado desde plataforma)
 * - 1000000002: Dra. Laura García (ya existe)
 * - 1000000003: Dr. Andrés Torres (ya existe)
 * - 122332211: Administrador (ya existe)
 *
 * Este seeder crea:
 * - 5 pacientes adicionales con historias completas
 * - 40+ citas distribuidas en diferentes estados y fechas
 * - Ejecuciones, historias clínicas, recetas y valoraciones
 * - Lista de espera con pacientes
 * - Logs de auditoría para todas las acciones
 * - Órdenes médicas de diferentes tipos
 *
 * Ejecutar: php artisan db:seed --class=PresentacionSeeder
 */
class PresentacionSeeder extends Seeder
{
    private Empresa $empresa;
    private Portafolio $portParticular;
    private array $estados = [];
    private array $modalidades = [];
    private array $servicios = [];
    private array $medicos = [];
    private array $pacientes = [];

    public function run(): void
    {
        $this->command->info('🚀 Iniciando seed para presentación...');

        // Cargar datos base
        $this->cargarDatosBase();

        // Crear pacientes adicionales
        $this->crearPacientesAdicionales();

        // Crear citas para todas las vistas
        $this->crearCitasPresentacion();

        // Crear lista de espera
        $this->crearListaEspera();

        // Crear logs de auditoría
        $this->crearLogsAuditoria();

        // Resumen final
        $this->mostrarResumen();
    }

    private function cargarDatosBase(): void
    {
        $this->empresa = Empresa::where('nit', '900123456-1')->firstOrFail();
        $this->portParticular = Portafolio::where('empresa_id', $this->empresa->id)
            ->where('nombre_convenio', 'Particular')
            ->firstOrFail();

        // Estados de cita
        $this->estados = [
            'atendida'   => EstadoCita::where('nombre', 'Atendida')->firstOrFail(),
            'pendiente'  => EstadoCita::where('nombre', 'Pendiente')->firstOrFail(),
            'confirmada' => EstadoCita::where('nombre', 'Confirmada')->firstOrFail(),
            'cancelada'  => EstadoCita::where('nombre', 'Cancelada')->firstOrFail(),
            'no_asistio' => EstadoCita::where('nombre', 'No asistió')->firstOrFail(),
        ];

        // Modalidades
        $this->modalidades = [
            'presencial'   => ModalidadCita::where('nombre', 'Presencial')->firstOrFail(),
            'telemedicina' => ModalidadCita::where('nombre', 'Telemedicina')->firstOrFail(),
            'domiciliaria' => ModalidadCita::where('nombre', 'Domiciliaria')->firstOrFail(),
        ];

        // Servicios
        $this->servicios = Servicio::where('empresa_id', $this->empresa->id)
            ->whereIn('nombre', [
                'Consulta Medicina General',
                'Consulta Pediatría',
                'Electrocardiograma',
                'Ecografía Abdominal',
                'Toma de Muestras',
                'Control Prenatal',
            ])
            ->get()
            ->keyBy('nombre')
            ->toArray();

        // Médicos
        $medico1 = Medico::whereHas('usuario', fn($q) => $q->where('identificacion', '1000000002'))->firstOrFail();
        $medico2 = Medico::whereHas('usuario', fn($q) => $q->where('identificacion', '1000000003'))->firstOrFail();
        $this->medicos = [$medico1, $medico2];

        // Paciente existente
        $this->pacientes[] = Paciente::where('identificacion', '1098765432')->firstOrFail();

        $this->command->info('✓ Datos base cargados');
    }

    private function crearPacientesAdicionales(): void
    {
        $rolPaciente = Rol::where('nombre', 'paciente')->firstOrFail();

        $pacientesData = [
            [
                'identificacion' => '5234567890',
                'nombre' => 'María Elena Rodríguez',
                'email' => 'maria.rodriguez@email.com',
                'fecha_nacimiento' => '1985-07-22',
                'sexo' => 'F',
                'telefono' => '3109876543',
                'direccion' => 'Carrera 45 # 78-90, Bogotá',
                'antecedentes' => [
                    ['tipo' => 'personal', 'descripcion' => 'Diabetes gestacional 2019. Controlada actualmente.'],
                    ['tipo' => 'familiar', 'descripcion' => 'Madre con hipertensión. Padre fallecido por infarto.'],
                    ['tipo' => 'alergico', 'descripcion' => 'Alergia a yodo: rash cutáneo.'],
                    ['tipo' => 'farmacologico', 'descripcion' => 'Metformina 500 mg. Levotiroxina 50 mcg.'],
                ]
            ],
            [
                'identificacion' => '6543210987',
                'nombre' => 'José Antonio Pérez',
                'email' => 'jose.perez@email.com',
                'fecha_nacimiento' => '1962-11-05',
                'sexo' => 'M',
                'telefono' => '3152345678',
                'direccion' => 'Avenida 68 # 24-15, Bogotá',
                'antecedentes' => [
                    ['tipo' => 'personal', 'descripcion' => 'Diabetes mellitus tipo 2 desde 2015. Retinopatía diabética leve.'],
                    ['tipo' => 'quirurgico', 'descripcion' => 'Colecistectomía laparoscópica 2018.'],
                    ['tipo' => 'farmacologico', 'descripcion' => 'Insulina glargina 20 UI. Metformina 850 mg.'],
                ]
            ],
            [
                'identificacion' => '7890123456',
                'nombre' => 'Ana Lucía Martínez',
                'email' => 'ana.martinez@email.com',
                'fecha_nacimiento' => '1990-03-18',
                'sexo' => 'F',
                'telefono' => '3187654321',
                'direccion' => 'Calle 127 # 45-30, Bogotá',
                'antecedentes' => [
                    ['tipo' => 'otros', 'descripcion' => 'Gineco-obstétricos: 2 gestaciones, 2 partos. Ultimo parto 2022.'],
                    ['tipo' => 'alergico', 'descripcion' => 'Rinitis alérgica estacional.'],
                    ['tipo' => 'farmacologico', 'descripcion' => 'Loratadina ocasional.'],
                ]
            ],
            [
                'identificacion' => '8901234567',
                'nombre' => 'Luis Fernando Castro',
                'email' => 'luis.castro@email.com',
                'fecha_nacimiento' => '1975-09-12',
                'sexo' => 'M',
                'telefono' => '3209876543',
                'direccion' => 'Carrera 7 # 72-33, Bogotá',
                'antecedentes' => [
                    ['tipo' => 'personal', 'descripcion' => 'Dislipidemia mixta. Sobrepeso.'],
                    ['tipo' => 'familiar', 'descripcion' => 'Abuelo materno con ACV.'],
                    ['tipo' => 'farmacologico', 'descripcion' => 'Atorvastatina 20 mg.'],
                ]
            ],
            [
                'identificacion' => '9012345678',
                'nombre' => 'Carmen Rosa Vargas',
                'email' => 'carmen.vargas@email.com',
                'fecha_nacimiento' => '1958-05-28',
                'sexo' => 'F',
                'telefono' => '3123456789',
                'direccion' => 'Calle 53 # 27-18, Bogotá',
                'antecedentes' => [
                    ['tipo' => 'personal', 'descripcion' => 'Artrosis de rodillas bilateral. Hipotiroidismo.'],
                    ['tipo' => 'quirurgico', 'descripcion' => 'Histerectomía 2005.'],
                    ['tipo' => 'farmacologico', 'descripcion' => 'Levotiroxina 75 mcg. Paracetamol 500 mg.'],
                ]
            ],
        ];

        foreach ($pacientesData as $data) {
            // Crear usuario
            $usuario = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'nombre'         => $data['nombre'],
                    'identificacion' => $data['identificacion'],
                    'password'       => Hash::make('password'),
                    'rol_id'         => $rolPaciente->id,
                    'empresa_id'     => $this->empresa->id,
                    'activo'         => true,
                ]
            );

            // Crear paciente
            $paciente = Paciente::firstOrCreate(
                ['identificacion' => $data['identificacion'], 'empresa_id' => $this->empresa->id],
                [
                    'usuario_id'       => $usuario->id,
                    'portafolio_id'    => $this->portParticular->id,
                    'nombre_completo'  => $data['nombre'],
                    'fecha_nacimiento' => $data['fecha_nacimiento'],
                    'sexo'             => $data['sexo'],
                    'telefono'         => $data['telefono'],
                    'correo'           => $data['email'],
                    'direccion'        => $data['direccion'],
                ]
            );

            // Crear antecedentes
            foreach ($data['antecedentes'] as $ant) {
                AntecedentesPaciente::firstOrCreate(
                    ['paciente_id' => $paciente->id, 'tipo' => $ant['tipo']],
                    ['descripcion' => $ant['descripcion'], 'activo' => true]
                );
            }

            $this->pacientes[] = $paciente;
        }

        $this->command->info('✓ 5 pacientes adicionales creados');
    }

    private function crearCitasPresentacion(): void
    {
        // Fechas clave para la presentación
        $hoy = Carbon::now();
        $fechas = [
            'pasado_1' => $hoy->copy()->subDays(30),
            'pasado_2' => $hoy->copy()->subDays(15),
            'pasado_3' => $hoy->copy()->subDays(7),
            'hoy'      => $hoy,
            'futuro_1' => $hoy->copy()->addDays(3),
            'futuro_2' => $hoy->copy()->addDays(7),
            'futuro_3' => $hoy->copy()->addDays(14),
            'futuro_4' => $hoy->copy()->addDays(21),
        ];

        $horas = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];

        // Distribuir citas entre pacientes
        foreach ($this->pacientes as $pacienteIndex => $paciente) {
            // Citas ATENDIDAS (pasadas) - 2 por paciente
            for ($i = 0; $i < 2; $i++) {
                $fecha = $i === 0 ? $fechas['pasado_1'] : $fechas['pasado_2'];
                $this->crearCitaCompleta([
                    'paciente'   => $paciente,
                    'fecha'      => $fecha->format('Y-m-d'),
                    'hora'       => $horas[array_rand($horas)],
                    'estado'     => 'atendida',
                    'modalidad'  => $this->modalidades['presencial'],
                    'servicio'   => $this->servicios['Consulta Medicina General'],
                    'medico'     => $this->medicos[array_rand($this->medicos)],
                    'conAtencion' => true,
                ]);
            }

            // Citas PENDIENTES (hoy y futuro cercano)
            $this->crearCitaCompleta([
                'paciente'   => $paciente,
                'fecha'      => $fechas['hoy']->format('Y-m-d'),
                'hora'       => '10:00',
                'estado'     => 'pendiente',
                'modalidad'  => $this->modalidades['presencial'],
                'servicio'   => $this->servicios['Consulta Medicina General'],
                'medico'     => $this->medicos[0],
                'conAtencion' => false,
            ]);

            // Citas CONFIRMADAS (futuro)
            for ($i = 0; $i < 2; $i++) {
                $fecha = $i === 0 ? $fechas['futuro_1'] : $fechas['futuro_3'];
                $modalidad = $i === 0 ? $this->modalidades['telemedicina'] : $this->modalidades['presencial'];
                $this->crearCitaCompleta([
                    'paciente'   => $paciente,
                    'fecha'      => $fecha->format('Y-m-d'),
                    'hora'       => $horas[array_rand($horas)],
                    'estado'     => 'confirmada',
                    'modalidad'  => $modalidad,
                    'servicio'   => $this->servicios[array_rand($this->servicios)],
                    'medico'     => $this->medicos[array_rand($this->medicos)],
                    'conAtencion' => false,
                ]);
            }

            // Citas CANCELADAS
            $this->crearCitaCompleta([
                'paciente'   => $paciente,
                'fecha'      => $fechas['pasado_3']->format('Y-m-d'),
                'hora'       => $horas[array_rand($horas)],
                'estado'     => 'cancelada',
                'modalidad'  => $this->modalidades['presencial'],
                'servicio'   => $this->servicios['Consulta Medicina General'],
                'medico'     => $this->medicos[array_rand($this->medicos)],
                'conAtencion' => false,
                'activo'     => false,
            ]);

            // Citas NO ASISTIÓ
            $this->crearCitaCompleta([
                'paciente'   => $paciente,
                'fecha'      => $fechas['pasado_3']->format('Y-m-d'),
                'hora'       => $horas[array_rand($horas)],
                'estado'     => 'no_asistio',
                'modalidad'  => $this->modalidades['presencial'],
                'servicio'   => $this->servicios['Consulta Medicina General'],
                'medico'     => $this->medicos[array_rand($this->medicos)],
                'conAtencion' => false,
            ]);
        }

        // Citas adicionales para el médico específico (Dra. García)
        for ($i = 0; $i < 10; $i++) {
            $fecha = $i < 5 ? $fechas['hoy'] : $fechas['futuro_2'];
            $this->crearCitaCompleta([
                'paciente'   => $this->pacientes[array_rand($this->pacientes)],
                'fecha'      => $fecha->copy()->addDays($i < 5 ? 0 : rand(1, 7))->format('Y-m-d'),
                'hora'       => $horas[$i % count($horas)],
                'estado'     => $i < 3 ? 'pendiente' : ($i < 5 ? 'confirmada' : 'confirmada'),
                'modalidad'  => $i % 3 === 0 ? $this->modalidades['telemedicina'] : $this->modalidades['presencial'],
                'servicio'   => $this->servicios[array_rand($this->servicios)],
                'medico'     => $this->medicos[0], // Dra. García
                'conAtencion' => false,
            ]);
        }

        $this->command->info('✓ Citas de presentación creadas');
    }

    private function crearCitaCompleta(array $data): Cita
    {
        $cita = Cita::firstOrCreate(
            [
                'paciente_id' => $data['paciente']->id,
                'fecha'       => $data['fecha'],
                'hora'        => $data['hora'],
            ],
            [
                'empresa_id'    => $this->empresa->id,
                'medico_id'     => $data['medico']->id,
                'estado_id'     => $this->estados[$data['estado']]->id,
                'modalidad_id'  => $data['modalidad']->id,
                'portafolio_id' => $this->portParticular->id,
                'servicio_id'   => $data['servicio']['id'],
                'activo'        => $data['activo'] ?? true,
                'link_videollamada' => $data['modalidad']->nombre === 'Telemedicina'
                    ? 'https://meet.jit.si/jlvs-cita-' . uniqid()
                    : null,
            ]
        );

        // Si la cita fue atendida, crear ejecución completa
        if ($data['conAtencion']) {
            $this->crearAtencionCompleta($cita, $data['paciente'], $data['medico']);
        }

        return $cita;
    }

    private function crearAtencionCompleta(Cita $cita, Paciente $paciente, Medico $medico): void
    {
        // Formato correcto de fecha para la ejecución
        $fechaStr = $cita->fecha instanceof Carbon ? $cita->fecha->format('Y-m-d') : $cita->fecha;

        // La hora puede venir como '16:00' o '16:00:00', normalizar a 'H:i:s'
        $horaStr = strlen($cita->hora) === 5 ? $cita->hora . ':00' : $cita->hora;
        $fechaHoraInicio = $fechaStr . ' ' . $horaStr;

        // Ejecución de cita
        $ejecucion = EjecucionCita::firstOrCreate(
            ['cita_id' => $cita->id],
            [
                'inicio_atencion'  => $fechaHoraInicio,
                'fin_atencion'     => Carbon::parse($fechaHoraInicio)
                    ->addMinutes(rand(15, 45))
                    ->format('Y-m-d H:i:s'),
                'duracion_minutos' => rand(15, 45),
            ]
        );

        // Signos vitales
        SignosVitales::firstOrCreate(
            ['ejecucion_cita_id' => $ejecucion->id],
            [
                'paciente_id'             => $paciente->id,
                'peso_kg'                 => rand(50, 95) + (rand(0, 99) / 100),
                'talla_cm'                => rand(150, 185),
                'presion_sistolica'       => rand(110, 150),
                'presion_diastolica'      => rand(70, 95),
                'temperatura_c'           => rand(36, 38) + (rand(0, 9) / 10),
                'frecuencia_cardiaca'     => rand(60, 100),
                'saturacion_oxigeno'      => rand(95, 99),
                'frecuencia_respiratoria' => rand(14, 22),
                'observaciones'           => 'Signos vitales estables durante la consulta.',
            ]
        );

        // Historia clínica
        $diagnosticos = [
            ['codigo' => 'J06.9', 'descripcion' => 'Infección aguda de vías respiratorias'],
            ['codigo' => 'I10', 'descripcion' => 'Hipertensión esencial'],
            ['codigo' => 'E11.9', 'descripcion' => 'Diabetes mellitus tipo 2'],
            ['codigo' => 'M25.5', 'descripcion' => 'Dolor articular'],
            ['codigo' => 'K30', 'descripcion' => 'Dispepsia'],
        ];
        $diag = $diagnosticos[array_rand($diagnosticos)];

        $historia = HistoriaClinica::firstOrCreate(
            ['ejecucion_cita_id' => $ejecucion->id],
            [
                'paciente_id'       => $paciente->id,
                'motivo_consulta'   => 'Consulta de control y seguimiento médico.',
                'enfermedad_actual' => 'Paciente refiere mejoría general. Sin síntomas agudos.',
                'antecedentes'      => [],
                'diagnostico'       => $diag['descripcion'],
                'codigo_cie10'      => $diag['codigo'],
                'descripcion_cie10' => $diag['descripcion'],
                'plan_tratamiento'  => 'Continuar tratamiento habitual. Control en 3 meses.',
                'evaluacion'        => 'Estable. Buena respuesta al tratamiento.',
                'observaciones'     => 'Paciente orientado sobre medidas preventivas.',
            ]
        );

        // Receta médica
        $medicamentos = [
            "1. Acetaminofén 500 mg — 1 tableta cada 8 horas por 5 días\n2. Ibuprofeno 400 mg — 1 tableta cada 12 horas por 3 días",
            "1. Losartán 50 mg — 1 tableta cada 24 horas\n2. Amlodipino 5 mg — 1 tableta cada 24 horas",
            "1. Metformina 500 mg — 1 tableta cada 12 horas con las comidas",
            "1. Omeprazol 20 mg — 1 cápsula cada 24 horas en ayunas por 14 días",
        ];

        RecetaMedica::firstOrCreate(
            ['historia_clinica_id' => $historia->id],
            [
                'medicamentos' => $medicamentos[array_rand($medicamentos)],
                'indicaciones' => "- Tomar los medicamentos según indicación médica.\n- No suspender sin consultar.\n- Acudir a control según programación.",
            ]
        );

        // Orden médica (50% de probabilidad)
        if (rand(0, 1) === 1) {
            $tiposOrden = ['laboratorio', 'imagen', 'medicina', 'terapia'];
            $tipo = $tiposOrden[array_rand($tiposOrden)];

            $descripciones = [
                'laboratorio' => 'Hemograma completo, glucosa en ayunas, perfil lipídico',
                'imagen'      => 'Radiografía de tórax posteroanterior',
                'medicina'    => 'Consulta especializada en cardiología',
                'terapia'     => 'Fisioterapia - 10 sesiones',
            ];

            OrdenMedica::firstOrCreate(
                [
                    'historia_clinica_id' => $historia->id,
                    'tipo' => $tipo,
                ],
                [
                    'paciente_id'       => $paciente->id,
                    'descripcion'       => $descripciones[$tipo],
                    'instrucciones'     => 'Programar cita para realizar el procedimiento indicado.',
                    'estado'            => ['pendiente', 'completada'][rand(0, 1)],
                ]
            );
        }

        // Valoración (solo para algunas citas)
        if (rand(0, 2) > 0) {
            $comentarios = [
                'Excelente atención, muy profesional.',
                'Buena atención, me resolvieron todas las dudas.',
                'Muy satisfecho con el servicio.',
                'La doctora fue muy amable y paciente.',
                'Rápido y eficiente.',
            ];

            Valoracion::firstOrCreate(
                ['cita_id' => $cita->id, 'paciente_id' => $paciente->id],
                [
                    'puntuacion' => rand(3, 5),
                    'comentario' => $comentarios[array_rand($comentarios)],
                ]
            );
        }
    }

    private function crearListaEspera(): void
    {
        $estados = ['esperando', 'asignado', 'descartado'];

        for ($i = 0; $i < 5; $i++) {
            ListaEspera::firstOrCreate(
                [
                    'empresa_id' => $this->empresa->id,
                    'paciente_id' => $this->pacientes[array_rand($this->pacientes)]->id,
                    'servicio_id' => $this->servicios['Consulta Medicina General']['id'],
                    'fecha_solicitada' => Carbon::now()->addDays(rand(1, 30))->format('Y-m-d'),
                ],
                [
                    'medico_id' => $this->medicos[array_rand($this->medicos)]->id,
                    'estado'    => $estados[array_rand($estados)],
                    'notas'     => 'Paciente solicitó cita urgente.',
                ]
            );
        }

        $this->command->info('✓ Lista de espera poblada');
    }

    private function crearLogsAuditoria(): void
    {
        $acciones = ['crear', 'actualizar', 'eliminar', 'ver'];
        $modelos = ['Cita', 'Paciente', 'HistoriaClinica', 'RecetaMedica', 'User'];

        // Obtener usuarios del sistema
        $usuarios = User::whereIn('identificacion', [
            '122332211',      // Admin
            '3333333',        // Gestor
            '1000000002',     // Médico 1
            '1000000003',     // Médico 2
            '1098765432',     // Paciente principal
        ])->get();

        for ($i = 0; $i < 50; $i++) {
            $usuario = $usuarios->random();

            LogAuditoria::create([
                'usuario_id' => $usuario->id,
                'empresa_id' => $this->empresa->id,
                'accion'     => $acciones[array_rand($acciones)],
                'modelo'     => $modelos[array_rand($modelos)],
                'modelo_id'  => rand(1, 100),
                'ip'         => '192.168.1.' . rand(1, 255),
                'detalles'   => ['metodo' => ['GET', 'POST', 'PUT', 'DELETE'][rand(0, 3)]],
                'created_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
            ]);
        }

        $this->command->info('✓ Logs de auditoría creados');
    }

    private function mostrarResumen(): void
    {
        $this->command->newLine();
        $this->command->info('╔════════════════════════════════════════════════════════╗');
        $this->command->info('║     SEED PARA PRESENTACIÓN COMPLETADO ✅              ║');
        $this->command->info('╠════════════════════════════════════════════════════════╣');
        $this->command->info('║  Usuarios disponibles:                                 ║');
        $this->command->info('║    • Admin:     122332211 / password                   ║');
        $this->command->info('║    • Gestor:    3333333 / password                     ║');
        $this->command->info('║    • Médico 1:  1000000002 / password (Dra. García)    ║');
        $this->command->info('║    • Médico 2:  1000000003 / password (Dr. Torres)     ║');
        $this->command->info('║    • Paciente:  1098765432 / password (Carlos M.)      ║');
        $this->command->info('║                                                        ║');
        $this->command->info('║  Datos creados:                                        ║');
        $this->command->info('║    • 6 pacientes totales                               ║');
        $this->command->info('║    • ~40 citas (atendidas, pendientes, confirmadas)    ║');
        $this->command->info('║    • Historias clínicas completas                      ║');
        $this->command->info('║    • Recetas y órdenes médicas                         ║');
        $this->command->info('║    • Valoraciones de pacientes                         ║');
        $this->command->info('║    • Lista de espera                                   ║');
        $this->command->info('║    • Logs de auditoría                                 ║');
        $this->command->info('╚════════════════════════════════════════════════════════╝');
        $this->command->newLine();
        $this->command->info('🎯 Listo para la presentación del 3-4-5 y 21 de mayo!');
    }
}
