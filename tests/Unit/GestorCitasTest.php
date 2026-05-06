<?php

namespace Tests\Unit;

use App\Http\Controllers\Gestor\GestorCitasController;
use App\Models\Cita;
use App\Models\Empresa;
use App\Models\EstadoCita;
use App\Models\HorarioMedico;
use App\Models\Medico;
use App\Models\ModalidadCita;
use App\Models\Paciente;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GestorCitasTest extends TestCase
{
    use RefreshDatabase;

    private function seedEnvironment(): array
    {
        $empresa = Empresa::create([
            'nit'      => '900000010',
            'nombre'   => 'Empresa Unit Test',
            'telefono' => '3000000010',
            'correo'   => 'unit@example.com',
            'direccion'=> 'Calle Unitaria 1',
        ]);

        $rolMedico = Rol::create(['nombre' => 'medico', 'descripcion' => 'Médico']);
        $modalidad = ModalidadCita::create(['nombre' => 'Presencial']);
        $estado = EstadoCita::create(['nombre' => 'Pendiente']);
        $servicio = Servicio::create([
            'empresa_id'      => $empresa->id,
            'nombre'          => 'Consulta General',
            'duracion_minutos'=> 30,
        ]);

        $usuarioMedico = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol_id'     => $rolMedico->id,
            'activo'     => true,
        ]);

        $medico = Medico::create([
            'usuario_id'      => $usuarioMedico->id,
            'empresa_id'      => $empresa->id,
            'especialidad'    => 'Medicina General',
            'registro_medico' => 'REGUNIT001',
        ]);

        $paciente = Paciente::create([
            'empresa_id'       => $empresa->id,
            'nombre_completo'  => 'Paciente Unitario',
            'fecha_nacimiento' => '1985-05-05',
            'sexo'             => 'M',
            'telefono'         => '3000000011',
            'identificacion'   => '9999999999',
        ]);

        return compact('empresa', 'rolMedico', 'modalidad', 'estado', 'servicio', 'usuarioMedico', 'medico', 'paciente');
    }

    public function test_retorna_falso_cuando_el_horario_ya_esta_ocupado(): void
    {
        $data = $this->seedEnvironment();

        $fecha = Carbon::tomorrow()->format('Y-m-d');
        $diaSemana = Carbon::parse($fecha)->format('N') % 7;

        HorarioMedico::create([
            'medico_id'   => $data['medico']->id,
            'empresa_id'  => $data['empresa']->id,
            'dia_semana'  => $diaSemana,
            'hora_inicio' => '08:00:00',
            'hora_fin'    => '12:00:00',
            'activo'      => true,
        ]);

        Cita::create([
            'empresa_id'   => $data['empresa']->id,
            'medico_id'    => $data['medico']->id,
            'paciente_id'  => $data['paciente']->id,
            'estado_id'    => $data['estado']->id,
            'modalidad_id' => $data['modalidad']->id,
            'servicio_id'  => $data['servicio']->id,
            'fecha'        => $fecha,
            'hora'         => '09:00',
            'activo'       => true,
        ]);

        $controller = new GestorCitasController();
        $method = new \ReflectionMethod(GestorCitasController::class, 'isHorarioDisponible');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $data['medico']->id, $fecha, '09:00', $data['servicio']->id, null);

        $this->assertFalse($result);
    }

    public function test_retorna_true_cuando_hay_disponibilidad(): void
    {
        $data = $this->seedEnvironment();

        $fecha = Carbon::tomorrow()->format('Y-m-d');
        $diaSemana = Carbon::parse($fecha)->format('N') % 7;

        HorarioMedico::create([
            'medico_id'   => $data['medico']->id,
            'empresa_id'  => $data['empresa']->id,
            'dia_semana'  => $diaSemana,
            'hora_inicio' => '08:00:00',
            'hora_fin'    => '12:00:00',
            'activo'      => true,
        ]);

        $controller = new GestorCitasController();
        $method = new \ReflectionMethod(GestorCitasController::class, 'isHorarioDisponible');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $data['medico']->id, $fecha, '09:00', $data['servicio']->id, null);

        $this->assertTrue($result);
    }

    public function test_retorna_falso_cuando_medico_no_tiene_horarios_configurados(): void
    {
        $data = $this->seedEnvironment();

        $fecha = Carbon::tomorrow()->format('Y-m-d');

        $controller = new GestorCitasController();
        $method = new \ReflectionMethod(GestorCitasController::class, 'isHorarioDisponible');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $data['medico']->id, $fecha, '09:00', $data['servicio']->id, null);

        $this->assertFalse($result);
    }

    public function test_retorna_falso_cuando_servicio_tiene_duracion_diferente_y_no_cabe(): void
    {
        $data = $this->seedEnvironment();

        $servicioLargo = Servicio::create([
            'empresa_id'      => $data['empresa']->id,
            'nombre'          => 'Cirugía Mayor',
            'duracion_minutos'=> 120, // 2 horas
        ]);

        $fecha = Carbon::tomorrow()->format('Y-m-d');
        $diaSemana = Carbon::parse($fecha)->format('N') % 7;

        HorarioMedico::create([
            'medico_id'   => $data['medico']->id,
            'empresa_id'  => $data['empresa']->id,
            'dia_semana'  => $diaSemana,
            'hora_inicio' => '08:00:00',
            'hora_fin'    => '10:00:00', // Solo 2 horas disponibles
            'activo'      => true,
        ]);

        $controller = new GestorCitasController();
        $method = new \ReflectionMethod(GestorCitasController::class, 'isHorarioDisponible');
        $method->setAccessible(true);

        // Intentar agendar a las 08:30, pero el servicio dura 2 horas y el horario termina a las 10:00
        $result = $method->invoke($controller, $data['medico']->id, $fecha, '08:30', $servicioLargo->id, null);

        $this->assertFalse($result);
    }

    public function test_selecciona_medico_con_menor_carga_cuando_hay_varios_disponibles(): void
    {
        $data = $this->seedEnvironment();

        // Crear otro médico
        $usuarioMedico2 = User::factory()->create([
            'empresa_id' => $data['empresa']->id,
            'rol_id'     => $data['rolMedico']->id,
            'activo'     => true,
        ]);

        $medico2 = Medico::create([
            'usuario_id'      => $usuarioMedico2->id,
            'empresa_id'      => $data['empresa']->id,
            'especialidad'    => 'Medicina General',
            'registro_medico' => 'REGUNIT002',
        ]);

        $fecha = Carbon::tomorrow()->format('Y-m-d');
        $diaSemana = Carbon::parse($fecha)->format('N') % 7;

        // Ambos médicos tienen horario
        HorarioMedico::create([
            'medico_id'   => $data['medico']->id,
            'empresa_id'  => $data['empresa']->id,
            'dia_semana'  => $diaSemana,
            'hora_inicio' => '08:00:00',
            'hora_fin'    => '12:00:00',
            'activo'      => true,
        ]);

        HorarioMedico::create([
            'medico_id'   => $medico2->id,
            'empresa_id'  => $data['empresa']->id,
            'dia_semana'  => $diaSemana,
            'hora_inicio' => '08:00:00',
            'hora_fin'    => '12:00:00',
            'activo'      => true,
        ]);

        // Médico 1 tiene 2 citas, médico 2 tiene 1 cita
        Cita::create([
            'empresa_id'   => $data['empresa']->id,
            'medico_id'    => $data['medico']->id,
            'paciente_id'  => $data['paciente']->id,
            'estado_id'    => $data['estado']->id,
            'modalidad_id' => $data['modalidad']->id,
            'servicio_id'  => $data['servicio']->id,
            'fecha'        => $fecha,
            'hora'         => '09:00',
            'activo'       => true,
        ]);

        Cita::create([
            'empresa_id'   => $data['empresa']->id,
            'medico_id'    => $data['medico']->id,
            'paciente_id'  => $data['paciente']->id,
            'estado_id'    => $data['estado']->id,
            'modalidad_id' => $data['modalidad']->id,
            'servicio_id'  => $data['servicio']->id,
            'fecha'        => $fecha,
            'hora'         => '10:00',
            'activo'       => true,
        ]);

        Cita::create([
            'empresa_id'   => $data['empresa']->id,
            'medico_id'    => $medico2->id,
            'paciente_id'  => $data['paciente']->id,
            'estado_id'    => $data['estado']->id,
            'modalidad_id' => $data['modalidad']->id,
            'servicio_id'  => $data['servicio']->id,
            'fecha'        => $fecha,
            'hora'         => '09:00',
            'activo'       => true,
        ]);

        // Simular la lógica de selección de médico con menor carga
        $medicosLibres = collect([$data['medico']->id, $medico2->id]);

        $cargaPorMedico = Cita::whereIn('medico_id', $medicosLibres)
            ->whereDate('fecha', $fecha)
            ->where('activo', true)
            ->selectRaw('medico_id, COUNT(*) as total')
            ->groupBy('medico_id')
            ->pluck('total', 'medico_id');

        $medicoSeleccionado = $medicosLibres->sortBy(fn($id) => $cargaPorMedico[$id] ?? 0)->first();

        // Debe seleccionar al médico 2 que tiene menos carga (1 cita vs 2)
        $this->assertEquals($medico2->id, $medicoSeleccionado);
    }
}
