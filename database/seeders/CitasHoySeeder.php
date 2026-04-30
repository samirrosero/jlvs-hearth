<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\ModalidadCita;
use App\Models\Paciente;
use App\Models\Portafolio;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Citas de prueba para HOY — las tres modalidades.
 *
 * Paciente : 1098765432 (Carlos Mendoza)
 * Doctora  : 1000000002 (Dra. Laura García)
 *
 * Ejecutar: php artisan db:seed --class=CitasHoySeeder
 */
class CitasHoySeeder extends Seeder
{
    public function run(): void
    {
        $hoy = now()->toDateString(); // 2026-04-28

        $medicoUser = User::where('identificacion', '1000000002')->firstOrFail();
        $medico     = $medicoUser->medico;

        $paciente = Paciente::where('identificacion', '1098765432')->firstOrFail();

        $portParticular = Portafolio::where('empresa_id', $medico->empresa_id)
            ->where('nombre_convenio', 'Particular')
            ->firstOrFail();

        $srvGeneral = Servicio::where('empresa_id', $medico->empresa_id)
            ->where('nombre', 'Consulta Medicina General')
            ->firstOrFail();

        $estadoPendiente  = EstadoCita::where('nombre', 'Pendiente')->firstOrFail();
        $estadoConfirmada = EstadoCita::where('nombre', 'Confirmada')->firstOrFail();

        $modalPresencial   = ModalidadCita::where('nombre', 'Presencial')->firstOrFail();
        $modalDomiciliaria = ModalidadCita::where('nombre', 'Domiciliaria')->firstOrFail();
        $modalTelemedicina = ModalidadCita::where('nombre', 'Telemedicina')->firstOrFail();

        $base = [
            'empresa_id'    => $medico->empresa_id,
            'medico_id'     => $medico->id,
            'paciente_id'   => $paciente->id,
            'portafolio_id' => $portParticular->id,
            'servicio_id'   => $srvGeneral->id,
            'activo'        => true,
        ];

        // ── 1. Presencial — 08:30 — Pendiente ────────────────────────
        $c1 = Cita::firstOrCreate(
            ['paciente_id' => $paciente->id, 'fecha' => $hoy, 'hora' => '08:30'],
            array_merge($base, [
                'estado_id'    => $estadoPendiente->id,
                'modalidad_id' => $modalPresencial->id,
            ])
        );

        // ── 2. Domiciliaria — 11:00 — Confirmada ─────────────────────
        $c2 = Cita::firstOrCreate(
            ['paciente_id' => $paciente->id, 'fecha' => $hoy, 'hora' => '11:00'],
            array_merge($base, [
                'estado_id'    => $estadoConfirmada->id,
                'modalidad_id' => $modalDomiciliaria->id,
            ])
        );

        // ── 3. Telemedicina — 14:30 — Confirmada ─────────────────────
        $c3 = Cita::firstOrCreate(
            ['paciente_id' => $paciente->id, 'fecha' => $hoy, 'hora' => '14:30'],
            array_merge($base, [
                'estado_id'         => $estadoConfirmada->id,
                'modalidad_id'      => $modalTelemedicina->id,
                'link_videollamada' => env('DEMO_MEET_LINK', 'https://meet.google.com/xxx-xxxx-xxx'),
            ])
        );

        // Si la cita ya existía pero no tiene link, lo actualiza
        if (! $c3->link_videollamada) {
            $c3->update(['link_videollamada' => env('DEMO_MEET_LINK', 'https://meet.google.com/xxx-xxxx-xxx')]);
        }

        $this->command->info("✓ 3 citas creadas para hoy ({$hoy}):");
        $this->command->info("  #{$c1->id} — 08:30 — Presencial   — Pendiente  — Dra. García / Carlos Mendoza");
        $this->command->info("  #{$c2->id} — 11:00 — Domiciliaria — Confirmada — Dra. García / Carlos Mendoza");
        $this->command->info("  #{$c3->id} — 14:30 — Telemedicina — Confirmada — Dra. García / Carlos Mendoza");
        $this->command->info("  → Sala Jitsi de la telemedicina: jlvs-cita-{$c3->id}");
    }
}
