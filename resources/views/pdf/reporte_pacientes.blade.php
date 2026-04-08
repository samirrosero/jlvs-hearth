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

        .footer { margin-top: 14px; font-size: 9px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="empresa">{{ $empresa->nombre }}</div>
        <div class="subtitulo">Reporte de Pacientes</div>
        <div class="meta">
            Generado el {{ now()->format('d/m/Y H:i') }}
            &nbsp;·&nbsp; Total: {{ $pacientes->count() }} pacientes
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre completo</th>
                <th>Identificación</th>
                <th>Nacimiento</th>
                <th>Sexo</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Cuenta</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pacientes as $p)
            <tr>
                <td>{{ $p->id }}</td>
                <td>{{ $p->nombre_completo }}</td>
                <td>{{ $p->identificacion }}</td>
                <td>{{ $p->fecha_nacimiento?->format('d/m/Y') }}</td>
                <td>{{ $p->sexo }}</td>
                <td>{{ $p->telefono }}</td>
                <td>{{ $p->correo ?? '—' }}</td>
                <td>{{ $p->usuario_id ? 'Sí' : 'No' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center; color:#9ca3af;">Sin resultados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">{{ $empresa->nombre }} · JLVS Hearth · Reporte generado automáticamente</div>
</body>
</html>
