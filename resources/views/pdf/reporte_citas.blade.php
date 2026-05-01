<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @php
        $cp  = $empresa->color_pdf       ?? '#1e40af';
        $cs  = $empresa->color_secundario ?? '#1e3a8a';
    @endphp
    <style>
        @page { margin: 2cm; }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a1a; line-height: 1.45; }

        /* ── Encabezado ── */
        .header {
            padding-bottom: 12px;
            margin-bottom: 0;
            border-bottom: 3px solid {{ $cp }};
        }
        .header table { border: none; width: 100%; }
        .header table td { border: none; padding: 0; }

        .empresa { font-size: 16px; font-weight: bold; color: {{ $cs }}; }
        .subtitulo { font-size: 12px; font-weight: bold; color: #374151; margin-top: 2px; }

        /* ── Barra de meta-info ── */
        .meta-bar {
            background-color: #eff6ff;
            border: 1px solid #dbeafe;
            border-top: none;
            padding: 5px 10px;
            font-size: 9px;
            color: #374151;
            margin-bottom: 14px;
        }
        .meta-bar strong { color: {{ $cp }}; }

        /* ── Tabla ── */
        table.datos { width: 100%; border-collapse: collapse; margin-top: 0; }
        table.datos thead tr { background-color: {{ $cp }}; color: #fff; }
        table.datos thead th {
            padding: 6px 7px;
            text-align: left;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: bold;
        }
        table.datos tbody tr:nth-child(even) { background-color: #f0f4ff; }
        table.datos tbody tr:hover { background-color: #e0eaff; }
        table.datos tbody td {
            padding: 5px 7px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9.5px;
            vertical-align: middle;
        }
        table.datos tfoot td {
            padding: 5px 7px;
            font-size: 9px;
            color: #6b7280;
            border-top: 2px solid #dbeafe;
            background-color: #f8faff;
        }

        /* ── Badges ── */
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8.5px; font-weight: bold; white-space: nowrap; }
        .badge-activo    { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge-inactivo  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge-pendiente { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
        .badge-atendido  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge-cancelado { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge-default   { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }

        /* ── Pie de página ── */
        .footer {
            margin-top: 14px;
            font-size: 8.5px;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 7px;
        }
    </style>
</head>
<body>

    {{-- ── ENCABEZADO ── --}}
    <div class="header">
        <table>
            <tr>
                <td style="width:70px; vertical-align:middle; padding-right:12px !important;">
                    <img src="{{ $empresa->logo_pdf_path }}" style="height:50px; width:auto;">
                </td>
                <td style="vertical-align:middle;">
                    <div class="empresa">{{ $empresa->nombre }}</div>
                    <div class="subtitulo">Reporte de Citas</div>
                    @if($empresa->nit)
                        <div style="font-size:9px; color:#6b7280; margin-top:2px;">NIT: {{ $empresa->nit }}</div>
                    @endif
                </td>
                <td style="vertical-align:middle; text-align:right;">
                    <div style="font-size:9px; color:#6b7280;">Generado el</div>
                    <div style="font-size:11px; font-weight:bold; color:{{ $cp }};">{{ now()->format('d/m/Y') }}</div>
                    <div style="font-size:9px; color:#6b7280;">{{ now()->format('H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── META-INFO ── --}}
    <div class="meta-bar">
        <strong>Total:</strong> {{ $citas->count() }} citas
        @if($filtros['fecha_desde'] ?? null)
            &nbsp;·&nbsp; <strong>Desde:</strong> {{ $filtros['fecha_desde'] }}
        @endif
        @if($filtros['fecha_hasta'] ?? null)
            &nbsp;·&nbsp; <strong>Hasta:</strong> {{ $filtros['fecha_hasta'] }}
        @endif
        @if($filtros['medico'] ?? null)
            &nbsp;·&nbsp; <strong>Médico:</strong> {{ $filtros['medico'] }}
        @endif
        @if($filtros['estado'] ?? null)
            &nbsp;·&nbsp; <strong>Estado:</strong> {{ $filtros['estado'] }}
        @endif
    </div>

    {{-- ── TABLA ── --}}
    <table class="datos">
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
                <td style="color:#9ca3af;">{{ $cita->id }}</td>
                <td>{{ $cita->fecha?->format('d/m/Y') }}</td>
                <td>{{ $cita->hora }}</td>
                <td><strong>{{ $cita->paciente?->nombre_completo }}</strong></td>
                <td>{{ $cita->medico?->usuario?->nombre }}</td>
                <td style="color:#6b7280;">{{ $cita->medico?->especialidad }}</td>
                <td>
                    @php
                        $estado = strtolower($cita->estado?->nombre ?? '');
                        $cls = match(true) {
                            str_contains($estado, 'atendid') || str_contains($estado, 'complet') => 'badge-atendido',
                            str_contains($estado, 'pendient') || str_contains($estado, 'programad') => 'badge-pendiente',
                            str_contains($estado, 'cancel') || str_contains($estado, 'ausent') => 'badge-cancelado',
                            default => 'badge-default'
                        };
                    @endphp
                    <span class="badge {{ $cls }}">{{ $cita->estado?->nombre ?? '—' }}</span>
                </td>
                <td>{{ $cita->modalidad?->nombre }}</td>
                <td>
                    <span class="badge {{ $cita->activo ? 'badge-activo' : 'badge-inactivo' }}">
                        {{ $cita->activo ? 'Sí' : 'No' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center; color:#9ca3af; padding:16px;">Sin resultados para los filtros aplicados.</td>
            </tr>
            @endforelse
        </tbody>
        @if($citas->count() > 0)
        <tfoot>
            <tr>
                <td colspan="9">Total: {{ $citas->count() }} registros</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        {{ $empresa->nombre }} &nbsp;·&nbsp; JLVS Hearth &nbsp;·&nbsp; Reporte generado automáticamente el {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
