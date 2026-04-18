<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @php
        $cp  = $empresa->color_pdf       ?? '#1e40af';
        $cs  = $empresa->color_secundario ?? '#1e3a8a';
    @endphp
    <style>
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
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8.5px; font-weight: bold; }
        .badge-si  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge-no  { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }

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
                    <div class="subtitulo">Reporte de Pacientes</div>
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
        <strong>Total registrados:</strong> {{ $pacientes->count() }} pacientes
    </div>

    {{-- ── TABLA ── --}}
    <table class="datos">
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
                <td style="color:#9ca3af;">{{ $p->id }}</td>
                <td><strong>{{ $p->nombre_completo }}</strong></td>
                <td>{{ $p->identificacion }}</td>
                <td>{{ $p->fecha_nacimiento?->format('d/m/Y') }}</td>
                <td>{{ $p->sexo }}</td>
                <td>{{ $p->telefono ?? '—' }}</td>
                <td style="color:#6b7280;">{{ $p->correo ?? '—' }}</td>
                <td>
                    <span class="badge {{ $p->usuario_id ? 'badge-si' : 'badge-no' }}">
                        {{ $p->usuario_id ? 'Sí' : 'No' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; color:#9ca3af; padding:16px;">Sin resultados.</td>
            </tr>
            @endforelse
        </tbody>
        @if($pacientes->count() > 0)
        <tfoot>
            <tr>
                <td colspan="8">Total: {{ $pacientes->count() }} registros</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        {{ $empresa->nombre }} &nbsp;·&nbsp; JLVS Hearth &nbsp;·&nbsp; Reporte generado automáticamente el {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
