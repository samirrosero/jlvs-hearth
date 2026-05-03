<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Mail\CitaAgendadaMail;
use App\Models\Cita;
use App\Models\HorarioMedico;
use App\Models\Medico;
use App\Models\ModalidadCita;
use App\Models\PrecioServicio;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AgendarCitaVistaController extends Controller
{
    private const SLOT_INICIO     = '06:00';
    private const SLOT_FIN        = '18:00';
    private const SLOT_MINUTOS    = 30;
    private const ESTADOS_LIBERAN = [4, 5]; // cancelada, no asistió
    private const ESTADO_PENDIENTE = 1;

    public function index()
    {
        $empresaId      = auth()->user()->empresa_id;
        $especialidades = Medico::where('empresa_id', $empresaId)
            ->distinct()
            ->orderBy('especialidad')
            ->pluck('especialidad');
        $modalidades    = ModalidadCita::all();
        $fechaMinima    = $this->fechaMinima();

        return view('paciente.agendar.index', compact('especialidades', 'modalidades', 'fechaMinima'));
    }

    public function disponible(Request $request)
    {
        $fechaMinima = $this->fechaMinima()->toDateString();

        $request->validate([
            'especialidad' => ['required', 'string', 'max:100'],
            'fecha'        => ['required', 'date', "after_or_equal:{$fechaMinima}"],
            'modalidad_id' => ['required', 'exists:modalidades_cita,id'],
        ], [
            'fecha.after_or_equal'  => 'Las citas en línea se agendan con mínimo 2 días hábiles de anticipación.',
            'modalidad_id.required' => 'Selecciona una modalidad de atención.',
        ]);

        $empresaId = auth()->user()->empresa_id;
        $fecha     = Carbon::parse($request->fecha);
        $diaSemana = (int) $fecha->format('w');

        $medicosIds = Medico::where('empresa_id', $empresaId)
            ->where('especialidad', 'like', "%{$request->especialidad}%")
            ->pluck('id');

        $horarios = HorarioMedico::whereIn('medico_id', $medicosIds)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->get();

        if ($horarios->isEmpty()) {
            return back()->withInput()->with('error',
                'No hay médicos disponibles para esa especialidad el día seleccionado. Intenta con otra fecha.'
            );
        }

        $slots = $this->generarFranjas($fecha, $medicosIds, $horarios);

        if ($slots->isEmpty()) {
            return back()->withInput()->with('error',
                'No quedan cupos disponibles para esa fecha. Por favor elige otra fecha.'
            );
        }

        $modalidades = ModalidadCita::all();

        // Precio según portafolio del paciente para un servicio de esta especialidad
        $paciente   = auth()->user()->paciente;
        $precio     = null;
        $portafolio = $paciente->portafolio;

        if ($portafolio) {
            $servicio = Servicio::where('empresa_id', $empresaId)
                ->where('nombre', 'like', "%{$request->especialidad}%")
                ->where('activo', true)
                ->first();

            if ($servicio) {
                $precio = PrecioServicio::where('servicio_id', $servicio->id)
                    ->where('portafolio_id', $portafolio->id)
                    ->value('precio');
            }
        }

        return view('paciente.agendar.disponible', [
            'slots'        => $slots,
            'fecha'        => $fecha,
            'especialidad' => $request->especialidad,
            'modalidad_id' => $request->modalidad_id,
            'modalidades'  => $modalidades,
            'portafolio'   => $portafolio,
            'precio'       => $precio,
        ]);
    }

    public function reservar(Request $request): RedirectResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'especialidad' => ['required', 'string', 'max:100'],
            'fecha'        => ['required', 'date', 'after_or_equal:' . $this->fechaMinima()->toDateString()],
            'hora'         => ['required', 'date_format:H:i'],
            'modalidad_id' => ['required', 'exists:modalidades_cita,id'],
        ]);

        $fecha     = Carbon::parse($data['fecha']);
        $hora      = $data['hora'];
        $horaFin   = Carbon::parse("{$data['fecha']} {$hora}")->addMinutes(self::SLOT_MINUTOS)->format('H:i:s');
        $diaSemana = (int) $fecha->format('w');

        // Médicos de la especialidad con horario activo que cubra el slot
        $medicosIds = Medico::where('empresa_id', $empresaId)
            ->where('especialidad', 'like', "%{$data['especialidad']}%")
            ->pluck('id');

        $medicosConHorario = HorarioMedico::whereIn('medico_id', $medicosIds)
            ->where('dia_semana', $diaSemana)
            ->where('hora_inicio', '<=', $hora)
            ->where('hora_fin', '>=', $horaFin)
            ->where('activo', true)
            ->pluck('medico_id')
            ->unique();

        // Excluir médicos con cita solapada
        $medicosOcupados = Cita::whereIn('medico_id', $medicosConHorario)
            ->whereDate('fecha', $fecha->toDateString())
            ->whereNotIn('estado_id', self::ESTADOS_LIBERAN)
            ->where('activo', true)
            ->whereRaw('hora < ?', [$horaFin])
            ->whereRaw('ADDTIME(hora, SEC_TO_TIME(? * 60)) > ?', [self::SLOT_MINUTOS, "{$hora}:00"])
            ->pluck('medico_id')
            ->unique();

        $medicosLibres = $medicosConHorario->diff($medicosOcupados);

        if ($medicosLibres->isEmpty()) {
            return back()->with('error', 'Ese cupo ya fue tomado. Por favor selecciona otro horario.');
        }

        // Balance de carga: médico con menos citas ese día
        $cargaPorMedico = Cita::whereIn('medico_id', $medicosLibres)
            ->whereDate('fecha', $fecha->toDateString())
            ->where('activo', true)
            ->selectRaw('medico_id, COUNT(*) as total')
            ->groupBy('medico_id')
            ->pluck('total', 'medico_id');

        $medicoId = $medicosLibres
            ->sortBy(fn ($id) => $cargaPorMedico[$id] ?? 0)
            ->first();

        // Buscar servicio y portafolio para asociarlos a la cita (necesario para precios)
        $paciente = auth()->user()->paciente;
        $servicio = Servicio::where('empresa_id', $empresaId)
            ->where('nombre', 'like', "%{$data['especialidad']}%")
            ->where('activo', true)
            ->first();

        $cita = Cita::create([
            'empresa_id'    => $empresaId,
            'medico_id'     => $medicoId,
            'paciente_id'   => $paciente->id,
            'estado_id'     => self::ESTADO_PENDIENTE,
            'modalidad_id'  => $data['modalidad_id'],
            'portafolio_id' => $paciente->portafolio_id,
            'servicio_id'   => $servicio?->id,
            'fecha'         => $data['fecha'],
            'hora'          => $hora,
            'activo'        => true,
        ]);

        // Si es telemedicina (modalidad_id=2), redirigir al formulario de pago ANTES de enviar correo
        $modalidad = ModalidadCita::find($data['modalidad_id']);
        $esTelemedicina = str_contains(strtolower($modalidad?->nombre ?? ''), 'telemedicina');

        if ($esTelemedicina) {
            return redirect()->route('paciente.citas.pago', $cita->id)
                ->with('info', 'Por favor realiza el pago para confirmar tu cita de telemedicina.');
        }

        // Para otras modalidades: enviar correo y redirigir a citas
        $correo = $paciente->correo ?? auth()->user()->email;
        if ($correo) {
            Mail::to($correo)->queue(
                new CitaAgendadaMail($cita->load('medico.usuario', 'paciente', 'estado', 'modalidad', 'empresa'))
            );
        }

        return redirect()->route('paciente.citas')
            ->with('success', "Cita agendada para el {$fecha->locale('es')->isoFormat('dddd D [de] MMMM')} a las {$hora}. Te enviaremos una confirmación por correo.");
    }

    private function fechaMinima(): Carbon
    {
        return Carbon::now()->addWeekdays(2);
    }

    private function generarFranjas(Carbon $fecha, $medicosIds, $horarios): \Illuminate\Support\Collection
    {
        $citasOcupadas = Cita::whereIn('medico_id', $medicosIds)
            ->whereDate('fecha', $fecha->toDateString())
            ->whereNotIn('estado_id', self::ESTADOS_LIBERAN)
            ->where('activo', true)
            ->get(['medico_id', 'hora']);

        $cursor = Carbon::parse($fecha->toDateString() . ' ' . self::SLOT_INICIO);
        $fin    = Carbon::parse($fecha->toDateString() . ' ' . self::SLOT_FIN);
        $slots  = collect();

        while ($cursor < $fin) {
            $horaSlot    = $cursor->format('H:i');
            $horaFinSlot = $cursor->copy()->addMinutes(self::SLOT_MINUTOS)->format('H:i:s');

            // Médicos con horario que cubre este slot
            $medicosEnSlot = $horarios
                ->filter(fn ($h) =>
                    $h->hora_inicio <= $horaSlot &&
                    $h->hora_fin    >= $horaFinSlot
                )
                ->pluck('medico_id')
                ->unique();

            // Médicos libres en este slot (sin cita solapada)
            $ocupadosEnSlot = $citasOcupadas
                ->filter(fn ($c) =>
                    $medicosEnSlot->contains($c->medico_id) &&
                    $c->hora < $horaFinSlot &&
                    Carbon::parse($fecha->toDateString() . ' ' . $c->hora)
                        ->addMinutes(self::SLOT_MINUTOS)
                        ->format('H:i:s') > "{$horaSlot}:00"
                )
                ->pluck('medico_id')
                ->unique();

            $disponibles = $medicosEnSlot->diff($ocupadosEnSlot)->count();

            if ($disponibles > 0) {
                $slots->push([
                    'hora'        => $horaSlot,
                    'hora_display'=> $cursor->format('g:i A'),
                    'cupos'       => $disponibles,
                ]);
            }

            $cursor->addMinutes(self::SLOT_MINUTOS);
        }

        return $slots;
    }
}
