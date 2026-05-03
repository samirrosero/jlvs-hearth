<?php

namespace Database\Seeders;

use App\Models\AntecedentesPaciente;
use App\Models\Cita;
use App\Models\EjecucionCita;
use App\Models\Empresa;
use App\Models\EstadoCita;
use App\Models\HistoriaClinica;
use App\Models\ModalidadCita;
use App\Models\Paciente;
use App\Models\Portafolio;
use App\Models\RecetaMedica;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\SignosVitales;
use App\Models\User;
use App\Models\Valoracion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Paciente de prueba con historia clínica completa.
 *
 * Usuario: samirarmero1610@gmail.com / password
 * Crea: perfil, antecedentes, 7 citas (3 atendidas, 1 cancelada,
 *       1 no asistió, 1 pendiente, 1 confirmada), signos vitales,
 *       historias clínicas, recetas y valoraciones.
 *
 * Ejecutar: php artisan db:seed --class=PacienteDemoSeeder
 */
class PacienteDemoSeeder extends Seeder
{
    public function run(): void
    {
        $empresa   = Empresa::where('nit', '900123456-1')->firstOrFail();
        $rolPac    = Rol::where('nombre', 'paciente')->firstOrFail();

        $portParticular = Portafolio::where('empresa_id', $empresa->id)
            ->where('nombre_convenio', 'Particular')->firstOrFail();

        $estadoAtendida   = EstadoCita::where('nombre', 'Atendida')->firstOrFail();
        $estadoPendiente  = EstadoCita::where('nombre', 'Pendiente')->firstOrFail();
        $estadoConfirmada = EstadoCita::where('nombre', 'Confirmada')->firstOrFail();
        $estadoCancelada  = EstadoCita::where('nombre', 'Cancelada')->firstOrFail();
        $estadoNoAsistio  = EstadoCita::where('nombre', 'No asistió')->firstOrFail();

        $modalPresencial  = ModalidadCita::where('nombre', 'Presencial')->firstOrFail();
        $modalTelemed     = ModalidadCita::where('nombre', 'Telemedicina')->firstOrFail();

        $srvGeneral = Servicio::where('empresa_id', $empresa->id)
            ->where('nombre', 'Consulta Medicina General')->firstOrFail();
        $srvEcg = Servicio::where('empresa_id', $empresa->id)
            ->where('nombre', 'Electrocardiograma')->firstOrFail();
        $srvMuestras = Servicio::where('empresa_id', $empresa->id)
            ->where('nombre', 'Toma de Muestras')->firstOrFail();
        $srvEcografia = Servicio::where('empresa_id', $empresa->id)
            ->where('nombre', 'Ecografía Abdominal')->firstOrFail();

        // médico 1 (Dra. García) vinculado a la empresa demo
        $medicoUser1 = User::where('email', 'dra.garcia@clinicademo.co')->firstOrFail();
        $medico1     = $medicoUser1->medico;

        $medicoUser2 = User::where('email', 'dr.torres@clinicademo.co')->firstOrFail();
        $medico2     = $medicoUser2->medico;

        // ── Usuario paciente ─────────────────────────────────────────
        $usuario = User::firstOrCreate(
            ['email' => 'samirarmero1610@gmail.com'],
            [
                'nombre'                 => 'Carlos Alberto Mendoza',
                'identificacion'         => '1098765432',
                'password'               => Hash::make('password'),
                'rol_id'                 => $rolPac->id,
                'empresa_id'             => $empresa->id,
                'activo'                 => true,
                'debe_cambiar_password'  => false,
            ]
        );

        // ── Perfil paciente ──────────────────────────────────────────
        $paciente = Paciente::firstOrCreate(
            ['identificacion' => '1098765432', 'empresa_id' => $empresa->id],
            [
                'usuario_id'       => $usuario->id,
                'portafolio_id'    => $portParticular->id,
                'nombre_completo'  => 'Carlos Alberto Mendoza Rivas',
                'fecha_nacimiento' => '1978-03-15',
                'sexo'             => 'M',
                'telefono'         => '3112345678',
                'correo'           => 'samirarmero1610@gmail.com',
                'direccion'        => 'Carrera 7 # 45-22 Apto 301, Bogotá',
                'nombre_aseguradora' => null,
                'numero_poliza'    => null,
            ]
        );

        // ── Antecedentes ─────────────────────────────────────────────
        $antecedentes = [
            ['tipo' => 'personal',      'descripcion' => 'Hipertensión arterial diagnosticada en 2018. En manejo farmacológico continuo.'],
            ['tipo' => 'familiar',      'descripcion' => 'Padre con diabetes mellitus tipo 2 (fallecido). Madre con hipertensión arterial.'],
            ['tipo' => 'quirurgico',    'descripcion' => 'Apendicectomía abierta en 2005. Sin complicaciones postoperatorias.'],
            ['tipo' => 'alergico',      'descripcion' => 'Alergia a la penicilina: urticaria generalizada. Reacción documentada en 2012.'],
            ['tipo' => 'farmacologico', 'descripcion' => 'Losartán 50 mg cada 24 horas. Amlodipino 5 mg cada 24 horas.'],
        ];

        foreach ($antecedentes as $ant) {
            AntecedentesPaciente::firstOrCreate(
                ['paciente_id' => $paciente->id, 'tipo' => $ant['tipo']],
                ['descripcion' => $ant['descripcion'], 'activo' => true]
            );
        }

        // ── Citas ─────────────────────────────────────────────────────
        // 1. Atendida — hace ~2 meses — Consulta Medicina General
        $cita1 = Cita::firstOrCreate(
            ['paciente_id' => $paciente->id, 'fecha' => '2026-02-27', 'hora' => '09:00'],
            [
                'empresa_id'   => $empresa->id,
                'medico_id'    => $medico1->id,
                'estado_id'    => $estadoAtendida->id,
                'modalidad_id' => $modalPresencial->id,
                'portafolio_id'=> $portParticular->id,
                'servicio_id'  => $srvGeneral->id,
                'activo'       => true,
            ]
        );

        // 2. Atendida — hace ~1 mes — Electrocardiograma
        $cita2 = Cita::firstOrCreate(
            ['paciente_id' => $paciente->id, 'fecha' => '2026-03-27', 'hora' => '10:30'],
            [
                'empresa_id'   => $empresa->id,
                'medico_id'    => $medico1->id,
                'estado_id'    => $estadoAtendida->id,
                'modalidad_id' => $modalPresencial->id,
                'portafolio_id'=> $portParticular->id,
                'servicio_id'  => $srvEcg->id,
                'activo'       => true,
            ]
        );

        // 3. Atendida — hace ~3 semanas — Consulta Medicina General (telemedicina)
        $cita3 = Cita::firstOrCreate(
            ['paciente_id' => $paciente->id, 'fecha' => '2026-04-06', 'hora' => '08:00'],
            [
                'empresa_id'   => $empresa->id,
                'medico_id'    => $medico2->id,
                'estado_id'    => $estadoAtendida->id,
                'modalidad_id' => $modalTelemed->id,
                'portafolio_id'=> $portParticular->id,
                'servicio_id'  => $srvGeneral->id,
                'activo'       => true,
            ]
        );

        // 4. Cancelada — hace ~2 semanas
        Cita::firstOrCreate(
            ['paciente_id' => $paciente->id, 'fecha' => '2026-04-13', 'hora' => '11:00'],
            [
                'empresa_id'   => $empresa->id,
                'medico_id'    => $medico1->id,
                'estado_id'    => $estadoCancelada->id,
                'modalidad_id' => $modalPresencial->id,
                'portafolio_id'=> $portParticular->id,
                'servicio_id'  => $srvGeneral->id,
                'activo'       => false,
            ]
        );

        // 5. No asistió — hace ~1 semana — Toma de Muestras
        Cita::firstOrCreate(
            ['paciente_id' => $paciente->id, 'fecha' => '2026-04-20', 'hora' => '07:30'],
            [
                'empresa_id'   => $empresa->id,
                'medico_id'    => $medico1->id,
                'estado_id'    => $estadoNoAsistio->id,
                'modalidad_id' => $modalPresencial->id,
                'portafolio_id'=> $portParticular->id,
                'servicio_id'  => $srvMuestras->id,
                'activo'       => true,
            ]
        );

        // 6. Pendiente — en 3 días
        Cita::firstOrCreate(
            ['paciente_id' => $paciente->id, 'fecha' => '2026-04-30', 'hora' => '09:30'],
            [
                'empresa_id'   => $empresa->id,
                'medico_id'    => $medico1->id,
                'estado_id'    => $estadoPendiente->id,
                'modalidad_id' => $modalPresencial->id,
                'portafolio_id'=> $portParticular->id,
                'servicio_id'  => $srvGeneral->id,
                'activo'       => true,
            ]
        );

        // 7. Confirmada — en ~2 semanas — Ecografía Abdominal
        Cita::firstOrCreate(
            ['paciente_id' => $paciente->id, 'fecha' => '2026-05-12', 'hora' => '14:00'],
            [
                'empresa_id'   => $empresa->id,
                'medico_id'    => $medico2->id,
                'estado_id'    => $estadoConfirmada->id,
                'modalidad_id' => $modalPresencial->id,
                'portafolio_id'=> $portParticular->id,
                'servicio_id'  => $srvEcografia->id,
                'activo'       => true,
            ]
        );

        // ── Ejecuciones, signos vitales e historias clínicas ──────────

        // --- Cita 1 ---
        $ejec1 = EjecucionCita::firstOrCreate(
            ['cita_id' => $cita1->id],
            [
                'inicio_atencion'  => '2026-02-27 09:00:00',
                'fin_atencion'     => '2026-02-27 09:22:00',
                'duracion_minutos' => 22,
            ]
        );

        SignosVitales::firstOrCreate(
            ['ejecucion_cita_id' => $ejec1->id],
            [
                'paciente_id'          => $paciente->id,
                'peso_kg'              => 82.50,
                'talla_cm'             => 173.00,
                'presion_sistolica'    => 148,
                'presion_diastolica'   => 94,
                'temperatura_c'        => 36.7,
                'frecuencia_cardiaca'  => 82,
                'saturacion_oxigeno'   => 97,
                'frecuencia_respiratoria' => 17,
                'observaciones'        => 'Presión arterial elevada. Paciente refiere estrés laboral.',
            ]
        );

        $hc1 = HistoriaClinica::firstOrCreate(
            ['ejecucion_cita_id' => $ejec1->id],
            [
                'paciente_id'      => $paciente->id,
                'motivo_consulta'  => 'Control de hipertensión arterial y cefalea occipital de 3 días de evolución.',
                'enfermedad_actual'=> 'Paciente masculino de 47 años con antecedente de HTA, acude por cefalea occipital pulsátil de 3 días de evolución, 7/10 en EVA, asociada a tensión cervical. Refiere toma irregular del medicamento antihipertensivo la semana anterior.',
                'antecedentes'     => ['HTA desde 2018', 'Apendicectomía 2005', 'Alérgico a la penicilina'],
                'diagnostico'      => 'Hipertensión arterial no controlada. Cefalea tensional.',
                'codigo_cie10'     => 'I10',
                'descripcion_cie10'=> 'Hipertensión esencial (primaria)',
                'plan_tratamiento' => 'Ajuste de Losartán a 100 mg/día. Amlodipino 5 mg se mantiene. Control en 4 semanas. Se recomienda dieta hiposódica, actividad física 30 min/día y seguimiento de tensión arterial en casa.',
                'evaluacion'       => 'Paciente con HTA mal controlada por adherencia irregular. Buen estado general, sin signos de daño de órgano blanco.',
                'observaciones'    => 'Se entregó material educativo sobre adherencia al tratamiento.',
            ]
        );

        RecetaMedica::firstOrCreate(
            ['historia_clinica_id' => $hc1->id],
            [
                'medicamentos' => "1. Losartán 100 mg — vía oral — 1 tableta cada 24 horas\n2. Amlodipino 5 mg — vía oral — 1 tableta cada 24 horas\n3. Acetaminofén 500 mg — vía oral — 1 tableta cada 8 horas por 3 días (para cefalea)",
                'indicaciones' => "- Tomar los antihipertensivos SIEMPRE a la misma hora, no suspender sin consultar.\n- Dieta baja en sal (menos de 5 g/día).\n- Evitar cafeína en exceso.\n- Medir presión en casa mañana y tarde; registrar valores.\n- Acudir a urgencias si la presión supera 180/110 mmHg.",
            ]
        );

        Valoracion::firstOrCreate(
            ['cita_id' => $cita1->id, 'paciente_id' => $paciente->id],
            ['puntuacion' => 5, 'comentario' => 'Excelente atención, la doctora explicó todo muy claramente y me dejó sin dudas.']
        );

        // --- Cita 2 ---
        $ejec2 = EjecucionCita::firstOrCreate(
            ['cita_id' => $cita2->id],
            [
                'inicio_atencion'  => '2026-03-27 10:30:00',
                'fin_atencion'     => '2026-03-27 11:05:00',
                'duracion_minutos' => 35,
            ]
        );

        SignosVitales::firstOrCreate(
            ['ejecucion_cita_id' => $ejec2->id],
            [
                'paciente_id'          => $paciente->id,
                'peso_kg'              => 81.80,
                'talla_cm'             => 173.00,
                'presion_sistolica'    => 136,
                'presion_diastolica'   => 86,
                'temperatura_c'        => 36.5,
                'frecuencia_cardiaca'  => 88,
                'saturacion_oxigeno'   => 98,
                'frecuencia_respiratoria' => 16,
                'observaciones'        => 'Mejora en cifras tensionales respecto a consulta previa.',
            ]
        );

        $hc2 = HistoriaClinica::firstOrCreate(
            ['ejecucion_cita_id' => $ejec2->id],
            [
                'paciente_id'      => $paciente->id,
                'motivo_consulta'  => 'Palpitaciones ocasionales y electrocardiograma de control.',
                'enfermedad_actual'=> 'Paciente acude por referir episodios de palpitaciones de inicio súbito y corta duración (menos de 30 segundos) en las últimas 2 semanas, sin síncope, disnea ni dolor precordial. Se solicita ECG para descartar arritmia.',
                'antecedentes'     => ['HTA en tratamiento', 'Alergia penicilina'],
                'diagnostico'      => 'Taquicardia sinusal. Hipertensión arterial en control.',
                'codigo_cie10'     => 'R00.0',
                'descripcion_cie10'=> 'Taquicardia, no especificada',
                'plan_tratamiento' => 'ECG actual sin alteraciones significativas. Se descarta arritmia de alto riesgo. Se indica manejo de factores desencadenantes: reducir cafeína, manejar estrés. Continuar antihipertensivos. Holter de 24 h si persisten síntomas.',
                'evaluacion'       => 'Palpitaciones probablemente relacionadas con estrés y consumo elevado de café. Presión arterial mejor controlada.',
                'observaciones'    => 'Se adjunta trazado de ECG en historia.',
            ]
        );

        RecetaMedica::firstOrCreate(
            ['historia_clinica_id' => $hc2->id],
            [
                'medicamentos' => "1. Losartán 100 mg — vía oral — 1 tableta cada 24 horas (continuar)\n2. Amlodipino 5 mg — vía oral — 1 tableta cada 24 horas (continuar)",
                'indicaciones' => "- Reducir consumo de café a máximo 1 taza/día.\n- Técnicas de relajación y manejo del estrés.\n- Consultar si las palpitaciones duran más de 5 minutos o se acompañan de mareo.",
            ]
        );

        Valoracion::firstOrCreate(
            ['cita_id' => $cita2->id, 'paciente_id' => $paciente->id],
            ['puntuacion' => 4, 'comentario' => 'Buena atención, me explicaron el resultado del electrocardiograma con detalle.']
        );

        // --- Cita 3 ---
        $ejec3 = EjecucionCita::firstOrCreate(
            ['cita_id' => $cita3->id],
            [
                'inicio_atencion'  => '2026-04-06 08:00:00',
                'fin_atencion'     => '2026-04-06 08:18:00',
                'duracion_minutos' => 18,
            ]
        );

        SignosVitales::firstOrCreate(
            ['ejecucion_cita_id' => $ejec3->id],
            [
                'paciente_id'          => $paciente->id,
                'peso_kg'              => 81.50,
                'talla_cm'             => 173.00,
                'presion_sistolica'    => 130,
                'presion_diastolica'   => 82,
                'temperatura_c'        => 37.2,
                'frecuencia_cardiaca'  => 90,
                'saturacion_oxigeno'   => 96,
                'frecuencia_respiratoria' => 19,
                'observaciones'        => 'Fiebre subjetiva en casa los últimos 2 días. Saturación en límite inferior aceptable.',
            ]
        );

        HistoriaClinica::firstOrCreate(
            ['ejecucion_cita_id' => $ejec3->id],
            [
                'paciente_id'      => $paciente->id,
                'motivo_consulta'  => 'Cuadro gripal de 3 días: fiebre, tos seca y malestar general.',
                'enfermedad_actual'=> 'Paciente con cuadro de 3 días de evolución caracterizado por fiebre hasta 38.1 °C, tos seca no productiva, odinofagia leve y mialgia generalizada. Sin disnea. Niega contacto conocido con COVID-19.',
                'antecedentes'     => ['HTA en tratamiento', 'Alergia penicilina'],
                'diagnostico'      => 'Infección aguda de las vías respiratorias superiores.',
                'codigo_cie10'     => 'J06.9',
                'descripcion_cie10'=> 'Infección aguda de las vías respiratorias superiores, no especificada',
                'plan_tratamiento' => 'Manejo sintomático: Acetaminofén 500 mg c/8 h por 5 días. Reposo relativo. Hidratación abundante. Lavados nasales con solución salina. Continuar antihipertensivos habituales. Control si no mejora en 5 días o presenta disnea.',
                'evaluacion'       => 'Cuadro viral autolimitado. Sin datos de complicación pulmonar.',
                'observaciones'    => 'Consulta por telemedicina. Paciente colaborador. Se descartó necesidad de antibiótico.',
            ]
        );

        $this->command->info('✓ Paciente demo creado: Carlos Alberto Mendoza');
        $this->command->info('  samirarmero1610@gmail.com / password');
        $this->command->info('  7 citas | 3 historias clínicas | 2 recetas | 2 valoraciones');
    }
}
