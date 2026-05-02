<?php

namespace App\Exports;

use App\Models\Medico;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MedicosExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private int $empresaId) {}

    public function query()
    {
        return Medico::where('empresa_id', $this->empresaId)
            ->with('usuario')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre completo',
            'Correo',
            'Identificación',
            'Especialidad',
            'Registro médico',
            'Activo',
            'Registrado el',
        ];
    }

    public function map($medico): array
    {
        return [
            $medico->id,
            $medico->usuario?->nombre ?? '—',
            $medico->usuario?->email ?? '—',
            $medico->usuario?->identificacion ?? '—',
            $medico->especialidad ?? '—',
            $medico->registro_medico ?? '—',
            $medico->usuario?->activo ? 'Sí' : 'No',
            $medico->created_at?->format('d/m/Y'),
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
        return 'Médicos';
    }
}
