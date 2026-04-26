<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; color: #1a1a1a; }
        .container { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1e40af; padding: 28px 32px; }
        .header h1 { color: #fff; font-size: 20px; margin: 0; }
        .header p { color: #bfdbfe; font-size: 13px; margin: 4px 0 0; }
        .body { padding: 28px 32px; }
        .greeting { font-size: 15px; margin-bottom: 16px; }
        .card { background: #eff6ff; border-left: 4px solid #1e40af; border-radius: 4px; padding: 16px 20px; margin: 20px 0; }
        .card-row { display: flex; margin-bottom: 8px; font-size: 14px; }
        .card-label { font-weight: bold; color: #374151; min-width: 150px; }
        .card-value { color: #1a1a1a; }
        .note { font-size: 13px; color: #6b7280; margin-top: 20px; line-height: 1.6; }
        .footer { background: #f9fafb; padding: 16px 32px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
@php
    $historia  = $historia;
    $paciente  = $historia->paciente;
    $empresa   = $paciente->empresa;
    $cita      = $historia->ejecucionCita?->cita;
    $medico    = $cita?->medico;
    $ejecucion = $historia->ejecucionCita;
@endphp
<div class="container">
    <div class="header">
        <h1>{{ $empresa->nombre ?? config('app.name') }}</h1>
        <p>Historia clínica digital</p>
    </div>

    <div class="body">
        <p class="greeting">
            Hola, <strong>{{ $paciente->nombre_completo }}</strong>.<br>
            Adjunto encontrarás tu historia clínica en formato PDF.
        </p>

        <div class="card">
            <div class="card-row">
                <span class="card-label">N.° de historia:</span>
                <span class="card-value">{{ str_pad($historia->id, 8, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="card-row">
                <span class="card-label">Fecha de atención:</span>
                <span class="card-value">
                    {{ $ejecucion?->inicio_atencion
                        ? \Carbon\Carbon::parse($ejecucion->inicio_atencion)->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
                        : \Carbon\Carbon::parse($historia->created_at)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                </span>
            </div>
            <div class="card-row">
                <span class="card-label">Médico tratante:</span>
                <span class="card-value">{{ $medico?->usuario?->nombre ?? '—' }}</span>
            </div>
            <div class="card-row">
                <span class="card-label">Especialidad:</span>
                <span class="card-value">{{ $medico?->especialidad ?? '—' }}</span>
            </div>
            <div class="card-row">
                <span class="card-label">Diagnóstico:</span>
                <span class="card-value">{{ $historia->diagnostico ?? '—' }}</span>
            </div>
        </div>

        <p class="note">
            Este documento es de carácter confidencial y contiene información clínica protegida.<br>
            Por favor guárdalo en un lugar seguro y no lo compartas con terceros no autorizados.<br><br>
            Este es un correo automático, por favor no respondas a este mensaje.
        </p>
    </div>

    <div class="footer">
        {{ $empresa->nombre ?? config('app.name') }}
        @if($empresa->telefono ?? null)
            &nbsp;·&nbsp; Tel: {{ $empresa->telefono }}
        @endif
        @if($empresa->direccion ?? null)
            &nbsp;·&nbsp; {{ $empresa->direccion }}
        @endif
    </div>
</div>
</body>
</html>
