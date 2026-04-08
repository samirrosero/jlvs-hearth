<?php

namespace App\Exports;

use App\Models\Cita;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CitasExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private int $empresaId,
        private array $filtros = []
    ) {}

    public function query()
    {
        $query = Cita::where('empresa_id', $this->empresaId)
            ->with(['medico.usuario', 'paciente', 'estado', 'modalidad', 'portafolio', 'servicio']);

        if (!empty($this->filtros['fecha_desde'])) {
            $query->whereDate('fecha', '>=', $this->filtros['fecha_desde']);
        }
        if (!empty($this->filtros['fecha_hasta'])) {
            $query->whereDate('fecha', '<=', $this->filtros['fecha_hasta']);
        }
        if (!empty($this->filtros['estado_id'])) {
            $query->where('estado_id', $this->filtros['estado_id']);
        }
        if (!empty($this->filtros['medico_id'])) {
            $query->where('medico_id', $this->filtros['medico_id']);
        }

        return $query->orderBy('fecha')->orderBy('hora');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha',
            'Hora',
            'Paciente',
            'Identificación Paciente',
            'Médico',
            'Especialidad',
            'Estado',
            'Modalidad',
            'Convenio',
            'Servicio',
            'Activo',
        ];
    }

    public function map($cita): array
    {
        return [
            $cita->id,
            $cita->fecha?->format('d/m/Y'),
            $cita->hora,
            $cita->paciente?->nombre_completo,
            $cita->paciente?->identificacion,
            $cita->medico?->usuario?->nombre,
            $cita->medico?->especialidad,
            $cita->estado?->nombre,
            $cita->modalidad?->nombre,
            $cita->portafolio?->nombre_convenio ?? 'Particular',
            $cita->servicio?->nombre ?? '—',
            $cita->activo ? 'Sí' : 'No',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Citas';
    }
}
