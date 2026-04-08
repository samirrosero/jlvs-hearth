<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a1a; line-height: 1.4; }

        .header { border-bottom: 2px solid #1e40af; padding-bottom: 10px; margin-bottom: 14px; }
        .empresa { font-size: 15px; font-weight: bold; color: #1e40af; }
        .subtitulo { font-size: 12px; font-weight: bold; color: #374151; margin-top: 2px; }
        .meta { font-size: 9px; color: #6b7280; }

        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        thead tr { background-color: #1e40af; color: #fff; }
        thead th { padding: 5px 6px; text-align: left; font-size: 9px; }
        tbody tr:nth-child(even) { background-color: #f0f4ff; }
        tbody td { padding: 4px 6px; border-bottom: 1px solid #e5e7eb; }

        .badge { padding: 1px 5px; border-radius: 3px; font-size: 9px; }
        .activo { background: #dcfce7; color: #166534; }
        .inactivo { background: #fee2e2; color: #991b1b; }

        .footer { margin-top: 14px; font-size: 9px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="empresa">{{ $empresa->nombre }}</div>
        <div class="subtitulo">Reporte de Citas</div>
        <div class="meta">
            Generado el {{ now()->format('d/m/Y H:i') }}
            @if($filtros['fecha_desde'] ?? null) &nbsp;·&nbsp; Desde: {{ $filtros['fecha_desde'] }} @endif
            @if($filtros['fecha_hasta'] ?? null) &nbsp;·&nbsp; Hasta: {{ $filtros['fecha_hasta'] }} @endif
            &nbsp;·&nbsp; Total: {{ $citas->count() }} citas
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Paciente</th>
                <th>Médico</th>
                <th>Especialidad</th>
                <th>Estado</th>
                <th>Modalidad</th>
                <th>Activo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($citas as $cita)
            <tr>
                <td>{{ $cita->id }}</td>
                <td>{{ $cita->fecha?->format('d/m/Y') }}</td>
                <td>{{ $cita->hora }}</td>
                <td>{{ $cita->paciente?->nombre_completo }}</td>
                <td>{{ $cita->medico?->usuario?->nombre }}</td>
                <td>{{ $cita->medico?->especialidad }}</td>
                <td>{{ $cita->estado?->nombre }}</td>
                <td>{{ $cita->modalidad?->nombre }}</td>
                <td>
                    <span class="badge {{ $cita->activo ? 'activo' : 'inactivo' }}">
                        {{ $cita->activo ? 'Sí' : 'No' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center; color:#9ca3af;">Sin resultados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">{{ $empresa->nombre }} · JLVS Hearth · Reporte generado automáticamente</div>
</body>
</html>
