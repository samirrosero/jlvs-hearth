<?php

namespace App\Exports;

use App\Models\Paciente;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PacientesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private int $empresaId,
        private array $filtros = []
    ) {}

    public function query()
    {
        $query = Paciente::where('empresa_id', $this->empresaId);

        if (!empty($this->filtros['sexo'])) {
            $query->where('sexo', $this->filtros['sexo']);
        }
        if (!empty($this->filtros['fecha_nacimiento_desde'])) {
            $query->whereDate('fecha_nacimiento', '>=', $this->filtros['fecha_nacimiento_desde']);
        }
        if (!empty($this->filtros['fecha_nacimiento_hasta'])) {
            $query->whereDate('fecha_nacimiento', '<=', $this->filtros['fecha_nacimiento_hasta']);
        }
        if (!empty($this->filtros['buscar'])) {
            $t = $this->filtros['buscar'];
            $query->where(fn ($q) => $q->where('nombre_completo', 'like', "%{$t}%")
                                       ->orWhere('identificacion', 'like', "%{$t}%"));
        }

        return $query->orderBy('nombre_completo');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre completo',
            'Identificación',
            'Fecha nacimiento',
            'Sexo',
            'Teléfono',
            'Correo',
            'Dirección',
            'Tiene cuenta',
            'Registrado el',
        ];
    }

    public function map($paciente): array
    {
        return [
            $paciente->id,
            $paciente->nombre_completo,
            $paciente->identificacion,
            $paciente->fecha_nacimiento?->format('d/m/Y'),
            $paciente->sexo,
            $paciente->telefono,
            $paciente->correo ?? '—',
            $paciente->direccion ?? '—',
            $paciente->usuario_id ? 'Sí' : 'No',
            $paciente->created_at?->format('d/m/Y'),
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
        return 'Pacientes';
    }
}
