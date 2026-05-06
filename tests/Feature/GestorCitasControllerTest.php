<?php

namespace Tests\Feature;

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

class GestorCitasControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createBaseData(): array
    {
        $empresa = Empresa::create([
            'nit'      => '900000001',
            'nombre'   => 'Empresa de Prueba',
            'telefono' => '3000000000',
            'correo'   => 'prueba@example.com',
            'direccion'=> 'Calle Falsa 123',
        ]);

        $rolGestor = Rol::create([
            'nombre'      => 'gestor_citas',
            'descripcion' => 'Gestor de citas',
        ]);

        $rolMedico = Rol::create([
            'nombre'      => 'medico',
            'descripcion' => 'Médico',
        ]);

        $modalidad = ModalidadCita::create(['nombre' => 'Presencial']);
        $estado = EstadoCita::create(['nombre' => 'Pendiente']);

        $paciente = Paciente::create([
            'empresa_id'       => $empresa->id,
            'nombre_completo'  => 'Paciente de Prueba',
            'fecha_nacimiento' => '1990-01-01',
            'sexo'             => 'M',
            'telefono'         => '3000000000',
            'identificacion'   => '1234567890',
        ]);

        $usuarioMedico = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol_id'     => $rolMedico->id,
            'activo'     => true,
        ]);

        $medico = Medico::create([
            'usuario_id'    => $usuarioMedico->id,
            'empresa_id'    => $empresa->id,
            'especialidad'  => 'Medicina General',
            'registro_medico' => 'REG123456',
        ]);

        return compact('empresa', 'rolGestor', 'rolMedico', 'modalidad', 'estado', 'paciente', 'usuarioMedico', 'medico');
    }

    public function test_un_gestor_no_puede_crear_cita_para_otra_empresa(): void
    {
        $data = $this->createBaseData();

        $otraEmpresa = Empresa::create([
            'nit'      => '900000002',
            'nombre'   => 'Otra Empresa',
            'telefono' => '3000000001',
            'correo'   => 'otra@example.com',
            'direccion'=> 'Avenida 1',
        ]);

        $usuarioGestor = User::factory()->create([
            'empresa_id' => $data['empresa']->id,
            'rol_id'     => $data['rolGestor']->id,
            'activo'     => true,
        ]);

        $usuarioMedicoOtraEmpresa = User::factory()->create([
            'empresa_id' => $otraEmpresa->id,
            'rol_id'     => $data['rolMedico']->id,
            'activo'     => true,
        ]);

        $medicoOtraEmpresa = Medico::create([
            'usuario_id'      => $usuarioMedicoOtraEmpresa->id,
            'empresa_id'      => $otraEmpresa->id,
            'especialidad'    => 'Medicina General',
            'registro_medico' => 'REG999999',
        ]);

        $response = $this->actingAs($usuarioGestor)->post(route('gestor.citas.store'), [
            'paciente_id'  => $data['paciente']->id,
            'medico_id'    => $medicoOtraEmpresa->id,
            'servicio_id'  => null,
            'modalidad_id' => $data['modalidad']->id,
            'estado_id'    => $data['estado']->id,
            'fecha'        => Carbon::tomorrow()->format('Y-m-d'),
            'hora'         => '09:00',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('citas', 0);
    }

    public function test_retorna_error_si_el_horario_del_medico_esta_ocupado(): void
    {
        $data = $this->createBaseData();

        $usuarioGestor = User::factory()->create([
            'empresa_id' => $data['empresa']->id,
            'rol_id'     => $data['rolGestor']->id,
            'activo'     => true,
        ]);

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
            'servicio_id'  => null,
            'fecha'        => $fecha,
            'hora'         => '09:00',
            'activo'       => true,
        ]);

        $response = $this->actingAs($usuarioGestor)->post(route('gestor.citas.store'), [
            'paciente_id'  => $data['paciente']->id,
            'medico_id'    => $data['medico']->id,
            'servicio_id'  => null,
            'modalidad_id' => $data['modalidad']->id,
            'estado_id'    => $data['estado']->id,
            'fecha'        => $fecha,
            'hora'         => '09:00',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'El horario está ocupado. No hay disponibilidad para esa hora.');
        $this->assertDatabaseCount('citas', 1);
    }
}
