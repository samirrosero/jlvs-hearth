<?php

namespace App\Http\Requests;

use App\Rules\MedicoDisponible;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    // Modalidades virtuales: Telemedicina=2, Domiciliaria=3
    private const MODALIDADES_VIRTUALES = [2, 3];

    private function reglasParaFecha(): array
    {
        $rol        = auth()->user()->rol?->nombre;
        $modalidad  = (int) $this->input('modalidad_id');
        $esVirtual  = in_array($modalidad, self::MODALIDADES_VIRTUALES);
        $esGestor   = in_array($rol, ['gestor_citas', 'administrador']);

        // Paciente virtual: mínimo 2 días de anticipación
        if ($esVirtual && !$esGestor) {
            $minFecha = Carbon::tomorrow()->addDay()->toDateString();
            return ['required', 'date', "after_or_equal:{$minFecha}"];
        }

        // Gestor/admin presencial: puede agendar el mismo día
        return ['required', 'date', 'after_or_equal:today'];
    }

    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;
        return [
            'medico_id'     => ['required', Rule::exists('medicos', 'id')->where('empresa_id', $empresaId), new MedicoDisponible()],
            'paciente_id'   => ['required', Rule::exists('pacientes', 'id')->where('empresa_id', $empresaId)],
            'estado_id'     => ['required', 'exists:estados_cita,id'],
            'modalidad_id'  => ['required', 'exists:modalidades_cita,id'],
            'portafolio_id' => ['nullable', Rule::exists('portafolios', 'id')->where('empresa_id', $empresaId)],
            'servicio_id'   => ['required', Rule::exists('servicios', 'id')->where('empresa_id', $empresaId)],
            'fecha'         => $this->reglasParaFecha(),
            'hora'          => ['required', 'date_format:H:i'],
            'activo'        => ['boolean'],
        ];
    }
}
