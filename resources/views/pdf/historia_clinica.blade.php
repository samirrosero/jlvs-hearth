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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.55;
        }

        /* ── Encabezado ── */
        .header {
            padding-bottom: 12px;
            margin-bottom: 18px;
            border-bottom: 3px solid {{ $cp }};
        }

        .header-grid {
            width: 100%;
        }

        .header-grid td {
            vertical-align: middle;
        }

        .empresa-nombre {
            font-size: 17px;
            font-weight: bold;
            color: {{ $cs }};
        }

        .empresa-detalle {
            font-size: 10px;
            color: #6b7280;
            margin-top: 1px;
        }

        .doc-titulo {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            color: {{ $cs }};
            letter-spacing: 0.3px;
        }

        .doc-numero {
            text-align: right;
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }

        /* ── Secciones ── */
        .seccion {
            margin-bottom: 16px;
        }

        .seccion-titulo {
            background-color: {{ $cp }};
            color: #ffffff;
            font-weight: bold;
            font-size: 9.5px;
            padding: 5px 10px;
            margin-bottom: 0;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .seccion-body {
            border: 1px solid #dbeafe;
            border-top: none;
            padding: 8px 10px;
            background-color: #ffffff;
        }

        .grid-2 {
            width: 100%;
            border-collapse: collapse;
        }

        .grid-2 tr:nth-child(even) td {
            background-color: #f8faff;
        }

        .grid-2 td {
            width: 50%;
            padding: 5px 8px;
            vertical-align: top;
            border-bottom: 1px solid #f0f4ff;
        }

        .label {
            font-weight: bold;
            color: #374151;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .valor {
            color: #111827;
            font-size: 11px;
            margin-top: 1px;
        }

        .campo-largo {
            padding: 7px 10px;
            border-left: 3px solid {{ $cp }};
            margin-bottom: 10px;
            color: #111827;
            background-color: #f8faff;
            font-size: 11px;
            line-height: 1.55;
        }

        .campo-label {
            font-weight: bold;
            color: {{ $cp }};
            font-size: 10px;
            margin-bottom: 3px;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ── Signos vitales ── */
        .signos-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .signos-grid td {
            border: 1px solid #dbeafe;
            padding: 6px 8px;
            text-align: center;
            font-size: 10px;
        }

        .signos-grid .sh {
            background-color: {{ $cp }};
            color: #ffffff;
            font-weight: bold;
            font-size: 9px;
            letter-spacing: 0.3px;
            padding: 5px 6px;
        }

        .signos-grid .sv {
            background-color: #eff6ff;
            font-size: 11px;
            font-weight: bold;
            color: {{ $cs }};
        }

        /* ── Recetas ── */
        .receta-box {
            border: 1px solid #dbeafe;
            border-left: 3px solid {{ $cp }};
            padding: 8px 10px;
            margin-bottom: 8px;
            background-color: #f8faff;
        }

        /* ── Pie de página ── */
        .footer {
            border-top: 2px solid {{ $cp }};
            padding-top: 14px;
            margin-top: 24px;
        }

        .firma-grid {
            width: 100%;
        }

        .firma-grid td {
            width: 50%;
            text-align: center;
            padding: 0 30px;
        }

        .firma-linea {
            border-top: 1px solid #9ca3af;
            margin-top: 44px;
            padding-top: 5px;
            font-size: 10px;
            color: #374151;
        }

        .aviso-legal {
            margin-top: 16px;
            font-size: 8.5px;
            color: #9ca3af;
            text-align: center;
            line-height: 1.5;
        }

        .badge {
            display: inline-block;
            background-color: #dbeafe;
            color: {{ $cp }};
            border: 1px solid #93c5fd;
            padding: 1px 7px;
            border-radius: 3px;
            font-size: 9.5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    {{-- ── ENCABEZADO ── --}}
    <div class="header">
        <table class="header-grid">
            <tr>
                <td style="width:60px;vertical-align:middle;padding-right:12px;">
                    <img src="{{ $empresa->logo_pdf_path }}" style="height:52px;width:auto;">
                </td>
                <td style="width:60%">
                    <div class="empresa-nombre">{{ $empresa->nombre }}</div>
                    @if($empresa->nit)
                        <div class="empresa-detalle">NIT: {{ $empresa->nit }}</div>
                    @endif
                    @if($empresa->direccion)
                        <div class="empresa-detalle">{{ $empresa->direccion }}{{ $empresa->ciudad ? ' — ' . $empresa->ciudad : '' }}</div>
                    @endif
                    @if($empresa->telefono)
                        <div class="empresa-detalle">Tel: {{ $empresa->telefono }}</div>
                    @endif
                </td>
                <td style="width:40%">
                    <div class="doc-titulo">HISTORIA CLÍNICA</div>
                    <div class="doc-numero">N.° {{ str_pad($historia->id, 8, '0', STR_PAD_LEFT) }}</div>
                    <div class="doc-numero">Generado: {{ now()->format('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── DATOS DEL PACIENTE ── --}}
    <div class="seccion">
        <div class="seccion-titulo">Datos del Paciente</div>
        <div class="seccion-body">
        <table class="grid-2">
            <tr>
                <td>
                    <span class="label">Nombre completo</span><br>
                    <span class="valor">{{ $paciente->nombre_completo }}</span>
                </td>
                <td>
                    <span class="label">Identificación</span><br>
                    <span class="valor">{{ $paciente->identificacion }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Fecha de nacimiento</span><br>
                    <span class="valor">{{ \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('d/m/Y') }}</span>
                </td>
                <td>
                    <span class="label">Sexo</span><br>
                    <span class="valor">{{ $paciente->sexo }}</span>
                </td>
            </tr>
            @if($paciente->telefono || $paciente->correo)
            <tr>
                @if($paciente->telefono)
                <td>
                    <span class="label">Teléfono</span><br>
                    <span class="valor">{{ $paciente->telefono }}</span>
                </td>
                @endif
                @if($paciente->correo)
                <td>
                    <span class="label">Correo electrónico</span><br>
                    <span class="valor">{{ $paciente->correo }}</span>
                </td>
                @endif
            </tr>
            @endif
        </table>
        </div>
    </div>

    {{-- ── DATOS DE LA CONSULTA ── --}}
    @php
        $cita     = $historia->ejecucionCita?->cita;
        $medico   = $cita?->medico;
        $ejecucion = $historia->ejecucionCita;
    @endphp
    <div class="seccion">
        <div class="seccion-titulo">Datos de la Consulta</div>
        <div class="seccion-body">
        <table class="grid-2">
            <tr>
                <td>
                    <span class="label">Médico tratante</span><br>
                    <span class="valor">{{ $medico?->usuario?->nombre ?? '—' }}</span>
                </td>
                <td>
                    <span class="label">Especialidad</span><br>
                    <span class="valor">{{ $medico?->especialidad ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Fecha de atención</span><br>
                    <span class="valor">
                        {{ $ejecucion?->inicio_atencion ? \Carbon\Carbon::parse($ejecucion->inicio_atencion)->format('d/m/Y H:i') : '—' }}
                    </span>
                </td>
                <td>
                    <span class="label">Duración</span><br>
                    <span class="valor">
                        {{ $ejecucion?->duracion_minutos ? $ejecucion->duracion_minutos . ' min' : '—' }}
                    </span>
                </td>
            </tr>
            @if($cita?->modalidad || $cita?->portafolio)
            <tr>
                @if($cita?->modalidad)
                <td>
                    <span class="label">Modalidad</span><br>
                    <span class="valor">{{ $cita->modalidad->nombre }}</span>
                </td>
                @endif
                @if($cita?->portafolio)
                <td>
                    <span class="label">Convenio</span><br>
                    <span class="valor">{{ $cita->portafolio->nombre_convenio }}</span>
                </td>
                @endif
            </tr>
            @endif
        </table>
        </div>
    </div>

    {{-- ── SIGNOS VITALES ── --}}
    @if($signosVitales)
    <div class="seccion">
        <div class="seccion-titulo">Signos Vitales</div>
        <div class="seccion-body" style="padding:0;">
        <table class="signos-grid">
            <tr>
                <td class="sh">Peso (kg)</td>
                <td class="sh">Talla (cm)</td>
                <td class="sh">P. Sistólica</td>
                <td class="sh">P. Diastólica</td>
                <td class="sh">Temp. °C</td>
                <td class="sh">FC (lpm)</td>
                <td class="sh">SpO₂ (%)</td>
                <td class="sh">FR (rpm)</td>
            </tr>
            <tr>
                <td class="sv">{{ $signosVitales->peso_kg ?? '—' }}</td>
                <td class="sv">{{ $signosVitales->talla_cm ?? '—' }}</td>
                <td class="sv">{{ $signosVitales->presion_sistolica ?? '—' }}</td>
                <td class="sv">{{ $signosVitales->presion_diastolica ?? '—' }}</td>
                <td class="sv">{{ $signosVitales->temperatura_c ?? '—' }}</td>
                <td class="sv">{{ $signosVitales->frecuencia_cardiaca ?? '—' }}</td>
                <td class="sv">{{ $signosVitales->saturacion_oxigeno ?? '—' }}</td>
                <td class="sv">{{ $signosVitales->frecuencia_respiratoria ?? '—' }}</td>
            </tr>
        </table>
        @if($signosVitales->observaciones)
            <div style="padding:6px 10px; font-size:10px; color:#374151; border-top:1px solid #dbeafe;">
                <strong>Observaciones:</strong> {{ $signosVitales->observaciones }}
            </div>
        @endif
        </div>
    </div>
    @endif

    {{-- ── CONTENIDO CLÍNICO ── --}}
    <div class="seccion">
        <div class="seccion-titulo">Contenido Clínico</div>
        <div class="seccion-body">

        <div class="campo-label">Motivo de consulta</div>
        <div class="campo-largo">{{ $historia->motivo_consulta }}</div>

        <div class="campo-label">Enfermedad actual</div>
        <div class="campo-largo">{{ $historia->enfermedad_actual }}</div>

        <div class="campo-label">
            Diagnóstico
            @if($historia->codigo_cie10)
                &nbsp;<span class="badge">CIE-10: {{ $historia->codigo_cie10 }}</span>
            @endif
        </div>
        <div class="campo-largo">{{ $historia->diagnostico }}
            @if($historia->descripcion_cie10)
                <br><span style="color:#6b7280; font-size:10px; font-style:italic;">{{ $historia->descripcion_cie10 }}</span>
            @endif
        </div>

        <div class="campo-label">Plan de tratamiento</div>
        <div class="campo-largo">{{ $historia->plan_tratamiento }}</div>

        @if($historia->evaluacion)
            <div class="campo-label">Evaluación</div>
            <div class="campo-largo">{{ $historia->evaluacion }}</div>
        @endif

        @if($historia->observaciones)
            <div class="campo-label">Observaciones</div>
            <div class="campo-largo" style="margin-bottom:0;">{{ $historia->observaciones }}</div>
        @endif

        </div>
    </div>

    {{-- ── RECETAS MÉDICAS ── --}}
    @if($historia->recetasMedicas->isNotEmpty())
    <div class="seccion">
        <div class="seccion-titulo">Recetas Médicas</div>
        <div class="seccion-body">
        @foreach($historia->recetasMedicas as $i => $receta)
        <div class="receta-box" style="{{ !$loop->last ? 'margin-bottom:8px;' : 'margin-bottom:0;' }}">
            <div style="font-size:9px; color:#6b7280; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.4px; font-weight:bold;">Receta {{ $i + 1 }}</div>
            <div class="campo-label">Medicamentos</div>
            <div style="margin-bottom:7px; color:#111827;">{{ $receta->medicamentos }}</div>
            <div class="campo-label">Indicaciones</div>
            <div style="color:#111827;">{{ $receta->indicaciones }}</div>
        </div>
        @endforeach
        </div>
    </div>
    @endif

    {{-- ── PIE DE FIRMA ── --}}
    <div class="footer">
        <table class="firma-grid">
            <tr>
                <td>
                    <div class="firma-linea">
                        <strong>{{ $medico?->usuario?->nombre ?? '—' }}</strong><br>
                        Reg. Médico: {{ $medico?->registro_medico ?? '—' }}<br>
                        {{ $medico?->especialidad ?? '' }}
                    </div>
                </td>
                <td>
                    <div class="firma-linea">
                        <strong>{{ $paciente->nombre_completo }}</strong><br>
                        C.C. {{ $paciente->identificacion }}<br>
                        Paciente
                    </div>
                </td>
            </tr>
        </table>
        <div class="aviso-legal">
            Documento generado electrónicamente por {{ $empresa->nombre }} · JLVS Hearth
            — Resolución 1995 de 1999 Minsalud Colombia.
            Este documento tiene validez como historia clínica digital.
        </div>
    </div>

</body>
</html>
