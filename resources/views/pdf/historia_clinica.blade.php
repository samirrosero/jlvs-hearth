<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.5;
        }

        /* ── Encabezado ── */
        .header {
            border-bottom: 2px solid #1e40af;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .header-grid {
            width: 100%;
        }

        .header-grid td {
            vertical-align: top;
        }

        .empresa-nombre {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
        }

        .empresa-detalle {
            font-size: 10px;
            color: #555;
        }

        .doc-titulo {
            text-align: right;
            font-size: 13px;
            font-weight: bold;
            color: #1e40af;
        }

        .doc-numero {
            text-align: right;
            font-size: 10px;
            color: #555;
        }

        /* ── Secciones ── */
        .seccion {
            margin-bottom: 14px;
        }

        .seccion-titulo {
            background-color: #1e40af;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
            padding: 4px 8px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .grid-2 {
            width: 100%;
            border-collapse: collapse;
        }

        .grid-2 td {
            width: 50%;
            padding: 3px 6px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            color: #374151;
            font-size: 10px;
        }

        .valor {
            color: #1a1a1a;
        }

        .campo-largo {
            padding: 5px 6px;
            border-left: 3px solid #e5e7eb;
            margin-bottom: 8px;
            color: #1a1a1a;
        }

        .campo-label {
            font-weight: bold;
            color: #374151;
            font-size: 10px;
            margin-bottom: 2px;
        }

        /* ── Signos vitales ── */
        .signos-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .signos-grid td {
            border: 1px solid #d1d5db;
            padding: 4px 8px;
            text-align: center;
            font-size: 10px;
        }

        .signos-grid .sh {
            background-color: #eff6ff;
            font-weight: bold;
            color: #1e40af;
        }

        /* ── Recetas ── */
        .receta-box {
            border: 1px solid #d1d5db;
            padding: 8px;
            margin-bottom: 6px;
            border-radius: 3px;
        }

        /* ── Pie de página ── */
        .footer {
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
            margin-top: 20px;
        }

        .firma-grid {
            width: 100%;
        }

        .firma-grid td {
            width: 50%;
            text-align: center;
            padding: 0 20px;
        }

        .firma-linea {
            border-top: 1px solid #374151;
            margin-top: 40px;
            padding-top: 4px;
            font-size: 10px;
        }

        .aviso-legal {
            margin-top: 14px;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }

        .badge {
            display: inline-block;
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
    </style>
</head>
<body>

    {{-- ── ENCABEZADO ── --}}
    <div class="header">
        <table class="header-grid">
            <tr>
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
        <table class="grid-2">
            <tr>
                <td>
                    <span class="label">Nombre completo:</span><br>
                    <span class="valor">{{ $paciente->nombre_completo }}</span>
                </td>
                <td>
                    <span class="label">Identificación:</span><br>
                    <span class="valor">{{ $paciente->identificacion }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Fecha de nacimiento:</span><br>
                    <span class="valor">{{ \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('d/m/Y') }}</span>
                </td>
                <td>
                    <span class="label">Sexo:</span><br>
                    <span class="valor">{{ $paciente->sexo }}</span>
                </td>
            </tr>
            @if($paciente->telefono || $paciente->correo)
            <tr>
                @if($paciente->telefono)
                <td>
                    <span class="label">Teléfono:</span><br>
                    <span class="valor">{{ $paciente->telefono }}</span>
                </td>
                @endif
                @if($paciente->correo)
                <td>
                    <span class="label">Correo:</span><br>
                    <span class="valor">{{ $paciente->correo }}</span>
                </td>
                @endif
            </tr>
            @endif
        </table>
    </div>

    {{-- ── DATOS DE LA CONSULTA ── --}}
    @php
        $cita     = $historia->ejecucionCita?->cita;
        $medico   = $cita?->medico;
        $ejecucion = $historia->ejecucionCita;
    @endphp
    <div class="seccion">
        <div class="seccion-titulo">Datos de la Consulta</div>
        <table class="grid-2">
            <tr>
                <td>
                    <span class="label">Médico:</span><br>
                    <span class="valor">{{ $medico?->usuario?->nombre ?? '—' }}</span>
                </td>
                <td>
                    <span class="label">Especialidad:</span><br>
                    <span class="valor">{{ $medico?->especialidad ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Fecha de atención:</span><br>
                    <span class="valor">
                        {{ $ejecucion?->inicio_atencion ? \Carbon\Carbon::parse($ejecucion->inicio_atencion)->format('d/m/Y H:i') : '—' }}
                    </span>
                </td>
                <td>
                    <span class="label">Duración:</span><br>
                    <span class="valor">
                        {{ $ejecucion?->duracion_minutos ? $ejecucion->duracion_minutos . ' min' : '—' }}
                    </span>
                </td>
            </tr>
            @if($cita?->modalidad || $cita?->portafolio)
            <tr>
                @if($cita?->modalidad)
                <td>
                    <span class="label">Modalidad:</span><br>
                    <span class="valor">{{ $cita->modalidad->nombre }}</span>
                </td>
                @endif
                @if($cita?->portafolio)
                <td>
                    <span class="label">Convenio:</span><br>
                    <span class="valor">{{ $cita->portafolio->nombre_convenio }}</span>
                </td>
                @endif
            </tr>
            @endif
        </table>
    </div>

    {{-- ── SIGNOS VITALES ── --}}
    @if($signosVitales)
    <div class="seccion">
        <div class="seccion-titulo">Signos Vitales</div>
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
                <td>{{ $signosVitales->peso_kg ?? '—' }}</td>
                <td>{{ $signosVitales->talla_cm ?? '—' }}</td>
                <td>{{ $signosVitales->presion_sistolica ?? '—' }}</td>
                <td>{{ $signosVitales->presion_diastolica ?? '—' }}</td>
                <td>{{ $signosVitales->temperatura_c ?? '—' }}</td>
                <td>{{ $signosVitales->frecuencia_cardiaca ?? '—' }}</td>
                <td>{{ $signosVitales->saturacion_oxigeno ?? '—' }}</td>
                <td>{{ $signosVitales->frecuencia_respiratoria ?? '—' }}</td>
            </tr>
        </table>
        @if($signosVitales->observaciones)
            <div style="margin-top:5px; font-size:10px; color:#555;">
                <strong>Observaciones:</strong> {{ $signosVitales->observaciones }}
            </div>
        @endif
    </div>
    @endif

    {{-- ── CONTENIDO CLÍNICO ── --}}
    <div class="seccion">
        <div class="seccion-titulo">Contenido Clínico</div>

        <div class="campo-label">Motivo de consulta</div>
        <div class="campo-largo">{{ $historia->motivo_consulta }}</div>

        <div class="campo-label">Enfermedad actual</div>
        <div class="campo-largo">{{ $historia->enfermedad_actual }}</div>

        <div class="campo-label">Diagnóstico
            @if($historia->codigo_cie10)
                <span class="badge">CIE-10: {{ $historia->codigo_cie10 }}</span>
            @endif
        </div>
        <div class="campo-largo">{{ $historia->diagnostico }}
            @if($historia->descripcion_cie10)
                <br><span style="color:#6b7280; font-size:10px;">{{ $historia->descripcion_cie10 }}</span>
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
            <div class="campo-largo">{{ $historia->observaciones }}</div>
        @endif
    </div>

    {{-- ── RECETAS MÉDICAS ── --}}
    @if($historia->recetasMedicas->isNotEmpty())
    <div class="seccion">
        <div class="seccion-titulo">Recetas Médicas</div>
        @foreach($historia->recetasMedicas as $receta)
        <div class="receta-box">
            <div class="campo-label">Medicamentos</div>
            <div style="margin-bottom:6px;">{{ $receta->medicamentos }}</div>
            <div class="campo-label">Indicaciones</div>
            <div>{{ $receta->indicaciones }}</div>
        </div>
        @endforeach
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
